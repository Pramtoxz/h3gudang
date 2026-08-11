<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFcmTokenRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'fcm_token' => ['required', 'string'],
        ];
    }
}
