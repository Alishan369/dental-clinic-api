<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MedicalDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id', 'type', 'file_name', 'file_path'
    ];

    protected $appends = ['file_url'];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}
