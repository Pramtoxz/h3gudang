<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTokoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'kd_toko' => ['required', 'string', 'max:10', Rule::unique('pgsql.pmov2.tbltoko', 'kd_toko')],
            'toko' => ['required', 'string', 'max:255'],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'npwp' => ['nullable', 'string', 'max:20'],
            'kategori' => ['nullable', 'string', 'max:50'],
            'kd_ahm' => ['nullable', 'string', 'max:10'],
            'toko_active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kd_toko' => 'kode toko',
            'toko' => 'nama toko',
            'no_telp' => 'nomor telepon',
            'kd_ahm' => 'kode AHM',
            'toko_active' => 'status toko',
        ];
    }
}
