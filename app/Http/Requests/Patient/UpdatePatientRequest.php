<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'email'   => 'nullable|email',
            'address' => 'nullable|string',
            'doctor_id'        => 'nullable|exists:users,id',
            'appointments'      => 'sometimes|array',
            'appointments.*.appointment_date' => 'required_with:appointments|date',
            'appointments.*.appointment_time' => 'nullable|date_format:H:i',
            'appointments.*.doctor_id'        => 'required_with:appointments|exists:users,id',
            'diseases'         => 'nullable|array',
            'diseases.*.id'    => 'required|exists:diseases,id',
            'diseases.*.notes'          => 'nullable|string',
        ];
    }
}
