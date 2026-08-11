<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPinRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'pin' => ['required', 'digits:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required' => 'PIN harus diisi',
            'pin.digits' => 'PIN harus 4 digit angka',
        ];
    }
}
