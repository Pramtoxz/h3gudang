<?php

namespace App\Http\Requests\Picking;

use App\Models\H3\LokasiRak;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanLokasiRakRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'kd_lokasi' => [
                Rule::requiredIf(! $this->sedangUbah()),
                'string',
                'max:50',
                Rule::unique('pgsql_dms.H3.tbllokasi_part', 'kd_lokasi')
                    ->ignore($this->lokasiYangDiubah()?->getKey(), 'kd_lokasi'),
            ],
            'fk_gudang_part' => [
                'required',
                'string',
                'max:50',
                Rule::exists('pgsql_dms.H3.tblgudang_part', 'kd_gudang_part'),
            ],
            'jenis_lokasi' => ['nullable', 'string', 'max:100'],
            'kapasitas' => ['required', 'integer', 'min:0'],
            'lokasi_part_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kd_lokasi' => 'kode lokasi',
            'fk_gudang_part' => 'gudang part',
            'jenis_lokasi' => 'jenis lokasi',
            'lokasi_part_active' => 'status',
        ];
    }

    public function messages(): array
    {
        return [
            'kd_lokasi.unique' => 'Kode lokasi sudah dipakai lokasi lain.',
            'fk_gudang_part.exists' => 'Gudang part tidak ditemukan.',
        ];
    }

    private function sedangUbah(): bool
    {
        return $this->lokasiYangDiubah() !== null;
    }

    private function lokasiYangDiubah(): ?LokasiRak
    {
        $lokasi = $this->route('lokasiRak');

        return $lokasi instanceof LokasiRak ? $lokasi : null;
    }
}
