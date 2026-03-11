<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Appointment extends Model
{
    use HasUuids;
    protected $table = 'appointments';

    protected $fillable = [
        'id',
        'patient_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'fee',
        'status',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
