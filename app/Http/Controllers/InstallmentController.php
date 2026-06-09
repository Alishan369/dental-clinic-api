<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\Payment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class InstallmentController extends Controller
{
    public function pay(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,online',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $installment = Installment::findOrFail($id);
            $payment = Payment::findOrFail($installment->payment_id);

            $installment->paid_amount += $request->amount;
            $installment->save();

            $payment->paid_amount += $request->amount;
            $payment->save();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'paid_installment',
                'model_type' => Installment::class,
                'model_id' => $installment->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Installment paid successfully',
                'installment' => $installment,
                'payment' => $payment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Installment payment failed: ' . $e->getMessage()], 500);
        }
    }

    public function getOverdue()
    {
        $installments = Installment::overdue()->with('payment.patient')->get();
        return response()->json($installments);
    }

    public function getUpcoming()
    {
        $installments = Installment::where('status', '!=', 'paid')
            ->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(7)])
            ->with('payment.patient')
            ->get();
            
        return response()->json($installments);
    }
}
