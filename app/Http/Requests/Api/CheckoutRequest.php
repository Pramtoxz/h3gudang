<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_address.max' => 'Alamat pengiriman maksimal 500 karakter',
            'notes.max' => 'Catatan maksimal 1000 karakter',
        ];
    }
}
