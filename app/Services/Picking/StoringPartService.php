<?php

namespace App\Services\Picking;

use App\Models\AdminUser;
use App\Models\H3\KartuStock;
use App\Support\Picking\AreaRak;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoringPartService
{
    public const PER_HALAMAN = 25;

    public const STATUS_BAWAAN = 'default';

    public function __construct(
        private readonly AreaOperatorService $areaOperator,
        private readonly SinkronisasiStoringService $sinkronisasi,
    ) {
    }

    /**
     * @param  array{area?: ?string, status?: ?string, tgl_dari?: ?string, tgl_sampai?: ?string, cari?: ?string, gudang?: ?string}  $saring
     */
    public function daftarStoring(AdminUser $user, array $saring): LengthAwarePaginator
    {
        $status = ($saring['status'] ?? null) ?: self::STATUS_BAWAAN;

        $query = DB::connection('pgsql_dms')
            ->table('H3.kartustok as k')
            ->leftJoin('H3.tblgudang_part as g', 'k.fk_gudang', '=', 'g.kd_gudang_part')
            ->whereNotNull('k.qty_diterima')
            ->where('k.fk_do', 'LIKE', '%RS%')
            ->selectRaw('k.fk_do')
            ->selectRaw('max(k.tgl_kartu) as tgl_kartu')
            ->selectRaw('max(k.fk_gudang) as fk_gudang')
            ->selectRaw('max(g.nm_gudang_part) as nm_gudang_part')
            ->selectRaw('count(*) as total_items')
            ->selectRaw('count(*) filter (where k.status_masuk = true) as done_items')
            ->selectRaw('coalesce(sum(k.qty_diterima), 0) as total_qty_diterima')
            ->selectRaw('coalesce(sum(k.qty_masuk), 0) as total_qty_masuk')
            ->groupBy('k.fk_do')
            ->orderByRaw('max(k.tgl_kartu) desc');

        $this->areaOperator->saring($query, $this->areaOperator->areaUntuk($user), 'k.kode_rak');
        $this->saringAreaRak($query, $saring['area'] ?? null);
        $this->saringGudang($query, $saring['gudang'] ?? null);
        $this->saringPencarian($query, $saring['cari'] ?? null);
        $this->saringWaktu($query, $status, $saring['tgl_dari'] ?? null, $saring['tgl_sampai'] ?? null);
        $this->saringStatus($query, $status);

        return $query->paginate(self::PER_HALAMAN)
            ->withQueryString()
            ->through(fn (object $baris): array => [
                'fk_do' => $baris->fk_do,
                'tgl_kartu' => $baris->tgl_kartu,
                'fk_gudang' => $baris->fk_gudang,
                'nm_gudang_part' => $baris->nm_gudang_part ?: $baris->fk_gudang,
                'total_items' => (int) $baris->total_items,
                'done_items' => (int) $baris->done_items,
                'total_qty_diterima' => (int) $baris->total_qty_diterima,
                'total_qty_masuk' => (int) $baris->total_qty_masuk,
                'status_storing' => ((int) $baris->done_items >= (int) $baris->total_items && (int) $baris->total_items > 0) ? 'Done' : 'Waiting',
            ]);
    }

    /**
     * @return array{dokumen: array{fk_do: string, fk_gudang: ?string, nm_gudang_part: ?string, tgl_kartu: ?string, total_items: int, done_items: int}, parts: list<array<string, mixed>>}
     */
    public function detailStoring(AdminUser $user, string $noPenerimaan): array
    {
        $query = DB::connection('pgsql_dms')
            ->table('H3.kartustok as k')
            ->leftJoin('public.tblpart as p', 'k.no_part', '=', 'p.kd_part')
            ->leftJoin('H3.tblgudang_part as g', 'k.fk_gudang', '=', 'g.kd_gudang_part')
            ->where('k.fk_do', $noPenerimaan)
            ->whereNotNull('k.qty_diterima')
            ->select([
                'k.id',
                'k.fk_do',
                'k.no_part',
                'p.nm_part',
                'k.kode_rak',
                'k.no_doos',
                'k.qty_diterima',
                'k.qty_masuk',
                'k.status_masuk',
                'k.fk_gudang',
                'g.nm_gudang_part',
                'k.tgl_kartu',
                'k.updated_at',
            ])
            ->orderBy('k.status_masuk', 'asc')
            ->orderBy('k.kode_rak', 'asc');

        $this->areaOperator->saring($query, $this->areaOperator->areaUntuk($user), 'k.kode_rak');

        $parts = $query->get()->map(fn (object $baris): array => [
            'id' => (int) $baris->id,
            'fk_do' => $baris->fk_do,
            'no_part' => $baris->no_part,
            'nm_part' => $baris->nm_part ?: '-',
            'kode_rak' => $baris->kode_rak,
            'area_rak' => AreaRak::untuk($baris->kode_rak),
            'no_doos' => $baris->no_doos,
            'qty_diterima' => (int) $baris->qty_diterima,
            'qty_masuk' => $baris->qty_masuk !== null ? (int) $baris->qty_masuk : null,
            'status_masuk' => (bool) $baris->status_masuk,
            'fk_gudang' => $baris->fk_gudang,
            'nm_gudang_part' => $baris->nm_gudang_part ?: $baris->fk_gudang,
            'tgl_kartu' => $baris->tgl_kartu,
            'waktu_done' => $baris->updated_at,
        ])->all();

        $pertama = $parts[0] ?? null;
        $totalItems = count($parts);
        $doneItems = count(array_filter($parts, fn (array $p): bool => $p['status_masuk']));

        return [
            'dokumen' => [
                'fk_do' => $noPenerimaan,
                'fk_gudang' => $pertama['fk_gudang'] ?? null,
                'nm_gudang_part' => $pertama['nm_gudang_part'] ?? null,
                'tgl_kartu' => $pertama['tgl_kartu'] ?? null,
                'total_items' => $totalItems,
                'done_items' => $doneItems,
            ],
            'parts' => $parts,
        ];
    }

    /**
     * @param  array{fk_do: string, no_part: string, kode_rak: string, qty_masuk: int}  $data
     */
    public function simpanMasukRak(AdminUser $user, array $data): KartuStock
    {
        abort_unless(
            $this->areaOperator->bolehMengerjakan($user, $data['kode_rak']),
            403,
            'Anda tidak memiliki wewenang untuk area rak ini.'
        );

        return DB::connection('pgsql_dms')->transaction(function () use ($data): KartuStock {
            $kartuStok = KartuStock::query()
                ->where('fk_do', $data['fk_do'])
                ->where('no_part', $data['no_part'])
                ->where('kode_rak', $data['kode_rak'])
                ->where('status_masuk', false)
                ->lockForUpdate()
                ->first();

            if (! $kartuStok) {
                throw ValidationException::withMessages([
                    'qty_masuk' => 'Item storing tidak ditemukan atau sudah selesai.',
                ]);
            }

            $kartuStok->qty_masuk = (int) $data['qty_masuk'];
            $kartuStok->status_masuk = true;
            $kartuStok->save();

            return $kartuStok;
        });
    }

    public function updateStatusPart(AdminUser $user, int $id, string $status): array
    {
        abort_unless(
            $user->it === 't' || $this->areaOperator->adalahAdminArea($user),
            403,
            'Hanya admin yang dapat melakukan aksi ini.'
        );

        return DB::connection('pgsql_dms')->transaction(function () use ($id, $status): array {
            $kartuStok = KartuStock::query()
                ->where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($status === 'waiting') {
                $kartuStok->qty_masuk = null;
                $kartuStok->status_masuk = false;
                $kartuStok->save();
            }

            return [
                'success' => true,
                'message' => 'Status part berhasil diubah.',
                'status' => $status,
            ];
        });
    }

    public function tandaiSemuaSelesai(AdminUser $user, string $noPenerimaan): int
    {
        $area = $this->areaOperator->areaUntuk($user);

        return DB::connection('pgsql_dms')->transaction(function () use ($noPenerimaan, $area): int {
            $query = KartuStock::query()
                ->where('fk_do', $noPenerimaan)
                ->whereNotNull('qty_diterima')
                ->where('status_masuk', false);

            if ($area !== null) {
                $query->whereRaw(AreaRak::ekspresiSql('kode_rak').' = ?', [$area]);
            }

            $items = $query->lockForUpdate()->get();
            $diperbarui = 0;

            foreach ($items as $item) {
                $item->qty_masuk = $item->qty_diterima;
                $item->status_masuk = true;
                $item->save();
                $diperbarui++;
            }

            return $diperbarui;
        });
    }

    public function syncPenerimaan(): array
    {
        return $this->sinkronisasi->jalankan();
    }

    private function saringAreaRak(Builder $query, ?string $area): void
    {
        if ($area === null || $area === '' || $area === 'SEMUA') {
            return;
        }

        $query->whereRaw(AreaRak::ekspresiSql('k.kode_rak').' = ?', [$area]);
    }

    private function saringGudang(Builder $query, ?string $gudang): void
    {
        if ($gudang === null || $gudang === '' || $gudang === 'SEMUA') {
            return;
        }

        $query->where('k.fk_gudang', $gudang);
    }

    private function saringPencarian(Builder $query, ?string $cari): void
    {
        $term = trim((string) $cari);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $q) use ($term): void {
            $q->where('k.fk_do', 'ilike', '%'.$term.'%')
                ->orWhere('k.no_part', 'ilike', '%'.$term.'%');
        });
    }

    private function saringWaktu(Builder $query, string $status, ?string $dari, ?string $sampai): void
    {
        if ($status === 'done' || $status === 'all') {
            if ($dari) {
                $query->where('k.tgl_kartu', '>=', $dari);
            }
            if ($sampai) {
                $query->where('k.tgl_kartu', '<=', $sampai);
            }
        }
    }

    private function saringStatus(Builder $query, string $status): void
    {
        if ($status === self::STATUS_BAWAAN) {
            $query->havingRaw('count(*) filter (where k.status_masuk = true) < count(*)');
        } elseif ($status === 'done') {
            $query->havingRaw('count(*) filter (where k.status_masuk = true) >= count(*)');
        }
    }
}
