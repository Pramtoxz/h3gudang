<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SetupPinRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'pin' => ['required', 'digits:4'],
            'pin_confirmation' => ['required', 'same:pin'],
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required' => 'PIN harus diisi',
            'pin.digits' => 'PIN harus 4 digit angka',
            'pin_confirmation.required' => 'Konfirmasi PIN harus diisi',
            'pin_confirmation.same' => 'Konfirmasi PIN tidak cocok',
        ];
    }
}
