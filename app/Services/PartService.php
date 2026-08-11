<?php

namespace App\Services;

use App\Models\PopularPart;
use App\Models\PublicSchema\Part;
use App\Models\PublicSchema\PartTipeKendaraan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PartService
{
    private const CACHE_TTL = 3600;

    private const RELASI = ['stock', 'product'];

    /**
     * @return array{items: Collection, hasMore: bool}
     */
    public function daftar(array $filter, int $page, int $limit): array
    {
        if (array_key_exists('vehicle_type', $filter) || array_key_exists('category', $filter)) {
            return $this->hasilFilter($filter, $page, $limit);
        }

        if (array_key_exists('search', $filter)) {
            return $this->hasilPencarian($filter, $page, $limit);
        }

        return $this->populerLaluReguler($page, $limit);
    }

    public function detail(string $partNumber): Part
    {
        return Part::with(self::RELASI)->where('kd_part', $partNumber)->firstOrFail();
    }

    public function cekStok(string $partCode): array
    {
        $part = Part::where('kd_part', $partCode)->first();

        if (! $part) {
            return ['available' => false, 'message' => 'Part not found', 'qty' => 0];
        }

        $stok = $part->stock()->first();

        if (! $stok) {
            return ['available' => false, 'message' => 'Stock not found', 'qty' => 0];
        }

        $tersedia = $stok->available;

        return [
            'available' => $stok->is_available,
            'message' => $stok->is_available ? "Available {$tersedia} pcs" : 'Not Available',
            'qty' => max(0, $tersedia),
            'qty_on_hand' => $stok->qty_on_hand,
            'qty_booking' => $stok->qty_booking,
            'min_stock' => $part->min_stok,
        ];
    }

    private function hasilFilter(array $filter, int $page, int $limit): array
    {
        $query = $this->queryBerharga();

        if (array_key_exists('vehicle_type', $filter)) {
            $kodePart = PartTipeKendaraan::where('fk_tipe_kendaraan', $filter['vehicle_type'])
                ->pluck('fk_part')
                ->all();

            if (! $kodePart) {
                return ['items' => new Collection, 'hasMore' => false];
            }

            $query->whereIn('kd_part', $kodePart);
        }

        if (array_key_exists('category', $filter)) {
            $query->where('fk_detail_sub_kelompok_part', $filter['category']);
        }

        if (array_key_exists('search', $filter)) {
            $this->terapkanPencarian($query, $filter['search']);
        }

        return $this->ambilHalaman($query->orderBy('nm_part'), $page, $limit);
    }

    private function hasilPencarian(array $filter, int $page, int $limit): array
    {
        $query = $this->queryBerharga();

        $this->terapkanPencarian($query, $filter['search']);

        if (array_key_exists('category', $filter)) {
            $query->where('fk_detail_sub_kelompok_part', $filter['category']);
        }

        return $this->ambilHalaman($query->orderBy('nm_part'), $page, $limit);
    }

    private function populerLaluReguler(int $page, int $limit): array
    {
        $totalPopuler = Cache::remember(
            'total_popular_parts',
            self::CACHE_TTL,
            fn (): int => PopularPart::count()
        );

        $lewati = ($page - 1) * $limit;

        if ($lewati < $totalPopuler) {
            $items = PopularPart::with('part')
                ->orderBy('peringkat')
                ->skip($lewati)
                ->take($limit)
                ->get()
                ->map(fn (PopularPart $populer) => $populer->part)
                ->filter()
                ->values();

            $hasMore = ($page * $limit) < $totalPopuler || $this->queryAktif()->exists();

            return ['items' => $this->muatRelasi($items), 'hasMore' => $hasMore];
        }

        $query = $this->queryAktif();

        $kodePopuler = Cache::remember(
            'popular_part_numbers',
            self::CACHE_TTL,
            fn (): array => PopularPart::pluck('kode_part')->all()
        );

        if ($kodePopuler) {
            $query->whereNotIn('kd_part', $kodePopuler);
        }

        return $this->ambilHalaman($query->orderBy('nm_part'), $page, $limit, $lewati - $totalPopuler);
    }

    private function ambilHalaman(Builder $query, int $page, int $limit, ?int $lewati = null): array
    {
        $lewati ??= ($page - 1) * $limit;

        $total = (clone $query)->count();

        $items = $query->skip($lewati)->take($limit)->get();

        return [
            'items' => $this->muatRelasi($items),
            'hasMore' => $total > ($lewati + $limit),
        ];
    }

    private function muatRelasi(Collection $items): Collection
    {
        return $items->loadMissing(self::RELASI);
    }

    /**
     * Part dengan HET nol tidak pernah ditampilkan karena belum punya harga jual.
     */
    private function queryBerharga(): Builder
    {
        return Part::query()->whereRaw('CAST(het AS NUMERIC) > 0');
    }

    private function queryAktif(): Builder
    {
        return $this->queryBerharga()->where('part_active', true);
    }

    private function terapkanPencarian(Builder $query, string $kataKunci): void
    {
        $query->where(function (Builder $q) use ($kataKunci): void {
            $q->where('kd_part', 'ILIKE', "%{$kataKunci}%")
                ->orWhere('nm_part', 'ILIKE', "%{$kataKunci}%");
        });
    }
}
