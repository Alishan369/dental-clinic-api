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
            'doctor_name' => $this->doctor?->user?->name,
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'appointment_date' => $this->appointment_date ? $this->appointment_date->format('Y-m-d') : null,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'type' => $this->type,
            'notes' => $this->notes,
            'cancelled_reason' => $this->cancelled_reason,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
