<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'roll' => [
                'required',
                'string',
                Rule::unique('students', 'roll')->ignore($this->student),
                'regex:/^[A-Z]{2}\d{4}$/' // Format: XX0000
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'roll.regex' => 'Roll number must be in format XX0000 (e.g., CS1234)'
        ];
    }
}