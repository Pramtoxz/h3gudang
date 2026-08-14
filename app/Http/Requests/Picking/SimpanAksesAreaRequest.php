<?php

namespace App\Http\Requests\Picking;

use App\Models\H3\AksesArea;
use App\Services\Picking\AksesAreaService;
use App\Support\Picking\AreaRak;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanAksesAreaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => $this->sedangUbah() ? ['nullable'] : $this->aturanEmail(),
            'level' => ['required', 'integer', Rule::in([AksesArea::LEVEL_ADMIN, AksesArea::LEVEL_PIC])],
            'area' => [
                Rule::requiredIf((int) $this->input('level') !== AksesArea::LEVEL_ADMIN),
                'nullable',
                'string',
                'max:50',
                Rule::in(AreaRak::daftar()),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'level' => 'level akses',
            'area' => 'area rak',
        ];
    }

    public function messages(): array
    {
        return [
            'email.in' => 'Pengguna ini belum diberi hak akses menu oleh IT.',
            'email.unique' => 'Email ini sudah punya baris akses area.',
            'area.required' => 'Area rak wajib diisi untuk level selain Admin.',
            'area.in' => 'Area rak tidak dikenali.',
        ];
    }

    /**
     * Pilihan email dibatasi di server, bukan hanya di dropdown, supaya tidak
     * ada baris akses yang dibuat untuk orang yang tidak bisa masuk aplikasi.
     */
    private function aturanEmail(): array
    {
        return [
            'required',
            'email',
            'max:50',
            Rule::in(app(AksesAreaService::class)->emailTerdaftar()),
            Rule::unique('pgsql_dms.H3.tbl_akses_mu', 'email'),
        ];
    }

    private function sedangUbah(): bool
    {
        return $this->route('aksesArea') instanceof AksesArea;
    }
}
