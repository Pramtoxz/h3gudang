<?php

namespace App\Http\Requests\Picking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanChannelRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'area' => ['required', 'string', 'max:100'],
            'kode_channel' => [
                'required',
                'string',
                'max:50',
                Rule::unique('pgsql_dms.H3.tbl_area_channel', 'kode_channel')
                    ->ignore($this->route('channel')?->getKey()),
            ],
            'nama_channel' => ['required', 'string', 'max:255'],
            'nama_invoice' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode_channel' => 'kode channel',
            'nama_channel' => 'nama channel',
            'nama_invoice' => 'nama invoice',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_channel.unique' => 'Kode channel sudah dipakai channel lain.',
        ];
    }
}
