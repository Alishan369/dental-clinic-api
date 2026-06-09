<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_code' => $this->patient_code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'dob' => $this->dob ? $this->dob->format('d-M-Y') : null,
            'gender' => $this->gender ? ucfirst($this->gender) : null,
            'age' => $this->dob ? \Carbon\Carbon::parse($this->dob)->age : null,
            'balance' => $this->payments ? $this->payments->sum('balance_amount') : 0,
            'nextAppointment' => $this->appointments
                ? $this->appointments->where('appointment_date', '>=', now()->startOfDay())
                                    ->where('status', '!=', 'cancelled')
                                    ->sortBy('appointment_date')
                                    ->first()?->appointment_date?->format('Y-m-d')
                : null,
            'medical_history' => $this->diseases ? $this->diseases->pluck('name')->toArray() : [],
            'created_at' => $this->created_at->format('d-M-Y H:i:s'),
            'updated_at' => $this->updated_at->format('d-M-Y H:i:s'),
        ];
    }
}
