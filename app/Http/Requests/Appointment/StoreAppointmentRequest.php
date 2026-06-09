<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'patient_id'       => 'required|uuid|exists:patients,id',
            'doctor_id'        => 'required|uuid|exists:doctors,id',
            'appointment_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time'       => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/'],
            'type'             => 'required|in:checkup,treatment,followup,emergency',
            'notes'            => 'nullable|string',
        ];
    }
}
