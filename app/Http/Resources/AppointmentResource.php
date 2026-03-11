<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'patient_id' => $this->patient_id,
            'patient_name' => $this->patient ? $this->patient->name : null,
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'doctor_id' => $this->doctor_id,
            'doctor_name' => $this->doctor ? $this->doctor->name : null,
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'appointment_date' => $this->appointment_date,
            'appointment_time' => $this->appointment_time,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
