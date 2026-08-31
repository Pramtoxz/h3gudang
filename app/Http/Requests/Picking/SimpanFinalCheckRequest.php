<?php

namespace App\Http\Requests\Picking;

use Illuminate\Foundation\Http\FormRequest;

class SimpanFinalCheckRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fk_do' => ['required', 'string'],
            'keterangan_final' => ['nullable', 'string', 'max:255'],
            'poli' => ['nullable', 'integer', 'min:0'],
            'kotak' => ['required', 'array', 'min:1'],
            'kotak.*.id' => ['required', 'integer'],
            'kotak.*.nomor_kotak' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kotak.required' => 'Isi nomor kotak untuk minimal satu part sebelum menyimpan.',
            'kotak.min' => 'Isi nomor kotak untuk minimal satu part sebelum menyimpan.',
            'kotak.*.nomor_kotak.required' => 'Nomor kotak tidak boleh kosong.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'fk_do' => 'nomor DO',
            'keterangan_final' => 'keterangan final',
            'poli' => 'jumlah koli',
            'kotak' => 'nomor kotak',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dataFinal(): array
    {
        return [
            'keterangan_final' => $this->input('keterangan_final'),
            'poli' => $this->filled('poli') ? (int) $this->input('poli') : null,
            'kotak' => $this->input('kotak', []),
        ];
    }
}
