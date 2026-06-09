<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreatmentType extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'base_cost',
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
    ];
}
