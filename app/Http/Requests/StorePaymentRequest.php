<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'   => 'required|exists:patients,id',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount'  => 'nullable|numeric|min:0',
        ];
    }
}
