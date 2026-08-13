<?php

namespace App\Http\Requests\Pengaturan;

use Illuminate\Foundation\Http\FormRequest;

class SimpanMenuRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'integer', 'exists:pgsql.projects,id'],
            'nama_menu' => ['required', 'string', 'max:255'],
            'ikon' => ['nullable', 'string', 'max:255'],
            'route' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:pgsql.menus,id'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'status_aktif' => ['boolean'],
            'khusus_it' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'project_id' => 'project',
            'nama_menu' => 'nama menu',
            'parent_id' => 'menu induk',
            'status_aktif' => 'status aktif',
            'khusus_it' => 'khusus IT',
        ];
    }
}
