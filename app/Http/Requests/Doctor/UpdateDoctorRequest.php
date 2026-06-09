<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => 'sometimes|string|max:255|min:2',
            'email'                 => 'sometimes|email|unique:users,email,' . $this->route('id'),
            'phone'                 => 'sometimes|string|max:20',
            'address'               => 'sometimes|string',
            'experience'            => 'sometimes|integer|min:0',
            'specialization'        => 'sometimes|string|max:255',
            'license_number'        => 'sometimes|string|max:100',
            'commission_percentage' => 'sometimes|numeric|min:0|max:100',
            'status'                => 'sometimes|in:active,inactive,pending',
            'password'              => 'sometimes|string|min:6',
        ];
    }
}
