<?php

namespace App\Http\Requests\Picking;

use App\Support\Picking\AreaRak;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HapusMassalLokasiRakRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'area_rak' => ['required', 'string', Rule::in(AreaRak::daftar())],
            'kode_gudang' => ['required', 'array', 'min:1'],
            'kode_gudang.*' => [
                'required',
                'string',
                Rule::exists('pgsql_dms.H3.tblgudang_part', 'kd_gudang_part'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'area_rak' => 'area rak',
            'kode_gudang' => 'gudang part',
            'kode_gudang.*' => 'gudang part',
        ];
    }

    public function messages(): array
    {
        return [
            'area_rak.in' => 'Area rak tidak dikenali.',
            'kode_gudang.*.exists' => 'Gudang part tidak ditemukan.',
        ];
    }
}
