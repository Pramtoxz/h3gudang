<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportExcelRequest extends FormRequest
{
    private const UKURAN_MAKSIMAL_KB = 5120;

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:'.self::UKURAN_MAKSIMAL_KB, 'extensions:xlsx,csv,txt'],
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'berkas',
        ];
    }

    public function messages(): array
    {
        return [
            'file.extensions' => 'Format berkas tidak valid. Gunakan Excel (.xlsx) atau CSV.',
        ];
    }

    public function ekstensi(): string
    {
        return strtolower($this->file('file')->getClientOriginalExtension());
    }

    public function lokasiBerkas(): string
    {
        return $this->file('file')->getPathname();
    }
}
