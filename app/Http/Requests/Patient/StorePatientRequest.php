<?php
namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:20',
            'email'            => 'nullable|email',
            'address'          => 'nullable|string',
            'doctor_id'        => 'nullable|exists:users,id',
            'appointments'      => 'required|array|min:1',
            'appointments.*.appointment_date' => 'required|date',
            'appointments.*.appointment_time' => 'nullable|date_format:H:i',
            'appointments.*.doctor_id'        => 'required|exists:users,id',
            'diseases'         => 'nullable|array',
            'diseases.*.id'    => 'required|exists:diseases,id',
            'diseases.*.notes'          => 'nullable|string',
        ];
    }
}
