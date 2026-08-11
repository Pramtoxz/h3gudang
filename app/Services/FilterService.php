<?php

namespace App\Services;

use App\Models\PublicSchema\PartCategory;
use App\Models\PublicSchema\TipeKendaraan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FilterService
{
    private const BATAS_HASIL = 20;

    public function tipeKendaraan(?string $kataKunci = null): Collection
    {
        $query = TipeKendaraan::query()
            ->select('kd_ptm as code', 'desc_tipe_cust as name')
            ->where('tipe_active', 1);

        $this->terapkanPencarian($query, 'desc_tipe_cust', $kataKunci);

        return $this->ambil($query, ['kd_ptm', 'desc_tipe_cust'], 'desc_tipe_cust');
    }

    public function kategoriPart(?string $kataKunci = null): Collection
    {
        $query = PartCategory::query()
            ->select('kd_detail_sub_kelompok_part as code', 'detail_sub_kelompok_part as name')
            ->where('active', true);

        $this->terapkanPencarian($query, 'detail_sub_kelompok_part', $kataKunci);

        return $this->ambil($query, ['kd_detail_sub_kelompok_part', 'detail_sub_kelompok_part'], 'detail_sub_kelompok_part');
    }

    private function terapkanPencarian(Builder $query, string $kolom, ?string $kataKunci): void
    {
        if (blank($kataKunci)) {
            return;
        }

        $query->where($kolom, 'ILIKE', "%{$kataKunci}%");
    }

    private function ambil(Builder $query, array $groupBy, string $orderBy): Collection
    {
        return $query->groupBy($groupBy)
            ->orderBy($orderBy)
            ->limit(self::BATAS_HASIL)
            ->get()
            ->map(fn ($item): array => [
                'code' => $item->code,
                'name' => $item->name,
            ]);
    }
}
