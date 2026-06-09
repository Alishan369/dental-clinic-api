<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository
{
    /**
     * Get all payments.
     */
    public function getAll(): Collection
    {
        return Payment::with('installments')->get();
    }

    /**
     * Find a payment by ID.
     */
    public function findById($id): ?Payment
    {
        return Payment::with('installments')->find($id);
    }

    /**
     * Create a new payment.
     */
    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    /**
     * Update an existing payment.
     */
    public function update(Payment $payment, array $data): bool
    {
        return $payment->update($data);
    }

    /**
     * Add an installment to a payment.
     */
    public function addInstallment(Payment $payment, array $data)
    {
        return $payment->installments()->create($data);
    }
}
