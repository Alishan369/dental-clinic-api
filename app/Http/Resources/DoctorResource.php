<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Doctor;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'name'                   => $this->user?->name,
            'email'                  => $this->user?->email,
            'phone'                  => $this->user?->phone,
            'address'                => $this->user?->address,
            'status'                 => $this->user?->status,
            'specialization'         => $this->specialization,
            'experience'             => $this->experience_years ?? 0,
            'license_number'         => $this->license_number,
            'commission_percentage'  => $this->commission_percentage ?? 0,
            'user_id'                => $this->user_id,
        ];
    }
}
