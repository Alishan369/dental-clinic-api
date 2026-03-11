<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Patient extends Model
{
    use HasUuids;
    protected $table = 'patients';

    protected $fillable = ['id', 'name', 'email', 'phone', 'address'];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function diseases()
    {
        return $this->belongsToMany(Disease::class, 'patient_diseases', 'patient_id', 'disease_id');
    }
}
