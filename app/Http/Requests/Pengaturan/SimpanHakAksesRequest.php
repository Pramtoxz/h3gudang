<?php

namespace App\Http\Requests\Pengaturan;

use Illuminate\Foundation\Http\FormRequest;

class SimpanHakAksesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'izin' => ['present', 'array'],
            'izin.*.menu_id' => ['required', 'integer', 'exists:pgsql.menus,id'],
            'izin.*.lihat' => ['boolean'],
            'izin.*.tambah' => ['boolean'],
            'izin.*.ubah' => ['boolean'],
            'izin.*.hapus' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'user',
            'izin' => 'daftar izin',
            'izin.*.menu_id' => 'menu',
        ];
    }
}
