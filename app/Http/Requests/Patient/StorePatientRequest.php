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
            'dob'              => 'nullable|date',
            'gender'           => 'nullable|string',
            'disease_ids'      => 'nullable|array',
            'disease_ids.*'    => 'exists:diseases,id',
        ];
    }
}
