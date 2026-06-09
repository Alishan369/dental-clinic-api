<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'patient_id', 'appointment_id', 'treatment_id', 'total_amount', 'discount_amount', 'final_amount', 'paid_amount', 'balance_amount', 'payment_type', 'payment_method', 'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($payment) {
            $payment->balance_amount = $payment->final_amount - $payment->paid_amount;
            if ($payment->balance_amount < 0) {
                $payment->balance_amount = 0;
            }

            if ($payment->paid_amount >= $payment->final_amount && $payment->final_amount > 0) {
                $payment->status = 'completed';
            } else {
                $payment->status = 'pending';
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(PatientTreatment::class, 'treatment_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }
}
