<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installment extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'payment_installments';

    protected $fillable = [
        'payment_id', 'due_date', 'amount', 'paid_amount', 'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($installment) {
            if ($installment->paid_amount >= $installment->amount && $installment->amount > 0) {
                $installment->status = 'paid';
            } elseif ($installment->due_date < now() && $installment->status !== 'paid') {
                $installment->status = 'overdue';
            }
        });
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')->orWhere(function($q) {
            $q->where('status', 'pending')->where('due_date', '<', now());
        });
    }
}
