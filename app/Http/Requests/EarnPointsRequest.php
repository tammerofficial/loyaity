<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EarnPointsRequest extends FormRequest
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
            'points' => 'required|integer|min:1|max:100000',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string|max:500',
            'reference_number' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'points.required' => 'Points amount is required.',
            'points.integer' => 'Points must be a whole number.',
            'points.min' => 'Points must be at least 1.',
            'points.max' => 'Points cannot exceed 100,000.',
            'amount.numeric' => 'Amount must be a valid number.',
            'currency.size' => 'Currency must be exactly 3 characters.',
        ];
    }
}
