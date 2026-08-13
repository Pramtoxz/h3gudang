<?php

namespace App\Http\Requests\Pmo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTokoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
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
            'toko' => 'nama toko',
            'no_telp' => 'nomor telepon',
            'kd_ahm' => 'kode AHM',
            'toko_active' => 'status toko',
        ];
    }
}
