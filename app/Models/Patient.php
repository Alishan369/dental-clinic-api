<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'patient_code', 'name', 'phone', 'email', 'address', 'dob', 'gender',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function patientTreatments(): HasMany
    {
        return $this->hasMany(PatientTreatment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function dentalRecords(): HasMany
    {
        return $this->hasMany(DentalRecord::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function diseases()
    {
        return $this->belongsToMany(Disease::class, 'patient_diseases')
                    ->withPivot('notes', 'other_disease_name')
                    ->withTimestamps();
    }
}
