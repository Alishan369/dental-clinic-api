<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|min:6',
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string',
            'specialization'  => 'nullable|string|max:255',
            'license_number'  => 'nullable|string|max:100|unique:doctors,license_number',
            'experience'      => 'nullable|integer|min:0',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
