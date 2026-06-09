<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Installment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'treatment_id' => 'required|exists:patient_treatments,id',
            'total_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,card,online',
            'payment_type' => 'required|in:full,installment',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $discount = $request->discount_amount ?? 0;
            $finalAmount = $request->total_amount - $discount;
            
            $isFullPayment = $request->payment_type === 'full';

            $payment = Payment::create([
                'patient_id' => $request->patient_id,
                'treatment_id' => $request->treatment_id,
                'total_amount' => $request->total_amount,
                'discount_amount' => $discount,
                'final_amount' => $finalAmount,
                'paid_amount' => $isFullPayment ? $finalAmount : 0,
                'payment_type' => $request->payment_type,
                'payment_method' => $request->payment_method,
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created_payment',
                'model_type' => Payment::class,
                'model_id' => $payment->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Payment created successfully', 'payment' => $payment], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Payment creation failed: ' . $e->getMessage()], 500);
        }
    }

    public function addInstallmentPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|exists:payments,id',
            'installment_count' => 'required|integer|min:2|max:12',
            'down_payment' => 'required|numeric|min:0',
            'interval_days' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($request->payment_id);
            if ($payment->payment_type !== 'installment') {
                return response()->json(['error' => 'Payment type must be installment to add a plan.'], 400);
            }

            if ($request->down_payment >= $payment->final_amount) {
                return response()->json(['error' => 'Down payment cannot be greater than or equal to final amount.'], 400);
            }

            // Record down payment
            $payment->paid_amount += $request->down_payment;
            $payment->save(); // Boot method will auto-calculate balance and status

            $remainingBalance = $payment->balance_amount;
            $installmentAmount = round($remainingBalance / $request->installment_count, 2);
            $intervalDays = $request->interval_days ?? 30;

            $installments = [];
            for ($i = 1; $i <= $request->installment_count; $i++) {
                // Adjust the last installment to account for rounding differences
                if ($i === (int)$request->installment_count) {
                    $installmentAmount = $remainingBalance - ($installmentAmount * ($request->installment_count - 1));
                }

                $installments[] = Installment::create([
                    'payment_id' => $payment->id,
                    'due_date' => Carbon::now()->addDays($intervalDays * $i)->toDateString(),
                    'amount' => $installmentAmount,
                    'paid_amount' => 0,
                    'status' => 'pending'
                ]);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created_installment_plan',
                'model_type' => Payment::class,
                'model_id' => $payment->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Installment plan created successfully',
                'payment' => $payment,
                'installments' => $installments
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Installment plan creation failed: ' . $e->getMessage()], 500);
        }
    }

    public function recordPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required_without:installment_id|exists:payments,id',
            'installment_id' => 'required_without:payment_id|exists:payment_installments,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,online',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            if ($request->has('installment_id')) {
                $installment = Installment::findOrFail($request->installment_id);
                $payment = Payment::findOrFail($installment->payment_id);

                $installment->paid_amount += $request->amount;
                $installment->save();

                $payment->paid_amount += $request->amount;
                $payment->save();

                $targetModel = $installment;
            } else {
                $payment = Payment::findOrFail($request->payment_id);
                $payment->paid_amount += $request->amount;
                $payment->save();
                
                $targetModel = $payment;
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'recorded_payment',
                'model_type' => get_class($targetModel),
                'model_id' => $targetModel->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Payment recorded successfully',
                'payment' => $payment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Payment recording failed: ' . $e->getMessage()], 500);
        }
    }

    public function getPendingPayments()
    {
        $payments = Payment::whereIn('status', ['pending', 'partial'])->get();
        return response()->json($payments);
    }

    public function getOverduePayments()
    {
        $payments = Payment::whereHas('installments', function($q) {
            $q->overdue();
        })->with(['installments' => function($q) {
            $q->overdue();
        }])->get();
        return response()->json($payments);
    }

    public function getDailyClosing()
    {
        $today = Carbon::today();
        
        // Sum total of all payments made today by fetching logs of payments and installments recorded today
        // This is a simplified approach, a true ledger would be much better
        
        $paymentSum = Payment::whereDate('updated_at', $today)->sum('paid_amount');
            
        return response()->json(['daily_closing' => $paymentSum]);
    }

    public function index(Request $request)
    {
        try {
            $query = Payment::with(['patient', 'treatment']);

            if ($request->filled('patient_id')) {
                $query->where('patient_id', $request->patient_id);
            }
            if ($request->filled('treatment_id')) {
                $query->where('treatment_id', $request->treatment_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $payments = $query->latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'Payments retrieved successfully',
                'data'    => $payments
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $payment = Payment::with(['patient', 'treatment', 'installments'])->find($id);
            if (!$payment) {
                return response()->json(['error' => 'Payment not found'], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Payment retrieved successfully',
                'data'    => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
