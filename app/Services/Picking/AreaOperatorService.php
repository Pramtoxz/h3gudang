<?php

namespace App\Services\Picking;

use App\Models\AdminUser;
use App\Models\H3\AksesArea;
use App\Support\Picking\AreaRak;
use Illuminate\Database\Query\Builder;

class AreaOperatorService
{
    /**
     * Area rak yang boleh dilihat user, atau null bila boleh melihat semua.
     *
     * Perilaku aplikasi lama dipertahankan: level 1 melihat semua area, dan
     * user yang tidak punya baris di `tbl_akses_mu` juga melihat semua. Yang
     * menjaga siapa boleh membuka halamannya tetap `menu_akses`.
     */
    public function areaUntuk(AdminUser $user): ?string
    {
        $akses = AksesArea::query()->where('email', $user->email)->first();

        if (! $akses || $akses->melihatSemuaArea()) {
            return null;
        }

        return $akses->area;
    }

    public function adalahAdminArea(AdminUser $user): bool
    {
        return $this->areaUntuk($user) === null;
    }

    /**
     * Menyaring baris ke satu area rak. Ekspresi areanya sama persis dengan
     * yang dipakai Master Lokasi Rak, jadi apa yang dilihat operator dan apa
     * yang dikelola admin tidak bisa berbeda tafsir.
     */
    public function saring(Builder $query, ?string $area, string $kolom = 'lokasi_part'): Builder
    {
        if ($area === null) {
            return $query;
        }

        return $query->whereRaw(AreaRak::ekspresiSql($kolom).' = ?', [$area]);
    }
}
