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
            'name'    => 'sometimes|string|max:255|min:2',
            'phone'   => 'sometimes|string|max:20',
            'email'   => 'sometimes|nullable|email',
            'address' => 'sometimes|string',
            'dob'     => 'sometimes|date',
            'gender'  => 'sometimes|in:male,female,other',
            'disease_ids'   => 'sometimes|nullable|array',
            'disease_ids.*' => 'exists:diseases,id',
        ];
    }
}
