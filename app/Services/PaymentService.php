<?php

namespace App\Services;

use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    protected $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function getAllPayments()
    {
        return $this->paymentRepository->getAll();
    }

    public function getPaymentById($id)
    {
        return $this->paymentRepository->findById($id);
    }

    public function createPayment(array $data)
    {
        $data['paid_amount'] = $data['paid_amount'] ?? 0;
        $data['remaining_amount'] = $data['total_amount'] - $data['paid_amount'];
        $data['status'] = $this->determineStatus($data['total_amount'], $data['paid_amount']);

        return $this->paymentRepository->create($data);
    }

    public function addInstallment($paymentId, array $data)
    {
        return DB::transaction(function () use ($paymentId, $data) {
            $payment = $this->paymentRepository->findById($paymentId);
            
            if (!$payment) {
                throw new Exception("Payment not found");
            }

            if ($payment->remaining_amount < $data['amount']) {
                throw new Exception("Installment amount exceeds remaining amount");
            }

            $installment = $this->paymentRepository->addInstallment($payment, [
                'amount' => $data['amount']
            ]);

            $newPaidAmount = $payment->paid_amount + $data['amount'];
            $newRemainingAmount = $payment->total_amount - $newPaidAmount;
            
            $this->paymentRepository->update($payment, [
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'status' => $this->determineStatus($payment->total_amount, $newPaidAmount),
            ]);

            return $installment;
        });
    }

    private function determineStatus($total, $paid)
    {
        if ($paid == 0) return 'unpaid';
        if ($paid >= $total) return 'paid';
        return 'partial';
    }
}
