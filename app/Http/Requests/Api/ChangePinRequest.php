<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ChangePinRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'old_pin' => ['required', 'digits:4'],
            'new_pin' => ['required', 'digits:4'],
            'new_pin_confirmation' => ['required', 'same:new_pin'],
        ];
    }

    public function messages(): array
    {
        return [
            'old_pin.required' => 'PIN lama harus diisi',
            'old_pin.digits' => 'PIN lama harus 4 digit angka',
            'new_pin.required' => 'PIN baru harus diisi',
            'new_pin.digits' => 'PIN baru harus 4 digit angka',
            'new_pin_confirmation.required' => 'Konfirmasi PIN baru harus diisi',
            'new_pin_confirmation.same' => 'Konfirmasi PIN baru tidak cocok',
        ];
    }
}
