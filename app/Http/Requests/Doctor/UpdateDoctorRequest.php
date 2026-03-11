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
            'name'       => 'required|string|max:255|min:2',
            'email'      => 'required|email|unique:users,email,' . $this->route('id'),
            'phone'      => 'required|string|max:20',
            'address'    => 'required|string',
            'experience' => 'nullable|integer|min:0',
        ];
    }
}
