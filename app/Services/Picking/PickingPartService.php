<?php

namespace App\Services\Picking;

use App\Models\AdminUser;
use App\Models\H3\AreaChannel;
use App\Models\H3\PickingInoma;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class PickingPartService
{
    public const PER_HALAMAN = 25;

    public const STATUS_BAWAAN = 'default';

    private const SUDAH_DIAMBIL = "count(*) filter (where p.status_picking_list in ('done','final'))";

    public function __construct(private readonly AreaOperatorService $areaOperator)
    {
    }

    /**
     * Pengelompokan per DO, penghitungan progres, penyaringan status, dan
     * paginasi seluruhnya dikerjakan database. Aplikasi lama menarik semua
     * baris ke PHP lalu mengelompokkan, menyaring, dan memaginasi di sana.
     *
     * @param  array{area?: ?string, status?: ?string, tgl_dari?: ?string, tgl_sampai?: ?string, cari?: ?string}  $saring
     */
    public function daftarDo(AdminUser $user, array $saring): LengthAwarePaginator
    {
        $status = ($saring['status'] ?? null) ?: self::STATUS_BAWAAN;

        $query = DB::connection('pgsql_dms')
            ->table('H3.tbl_picking_inoma as p')
            ->leftJoin('H3.tbl_area_channel as c', 'p.fk_dealer', '=', 'c.kode_channel')
            ->selectRaw('p.fk_do')
            ->selectRaw('max(p.tgl_picking_list_part) as tgl_picking_list_part')
            ->selectRaw('max(p.no_picking_list_part) as no_picking_list_part')
            ->selectRaw('max(p.fk_dealer) as fk_dealer')
            ->selectRaw('max(c.nama_channel) as nama_channel')
            ->selectRaw('max(c.area) as area')
            ->selectRaw('count(*) as total_items')
            ->selectRaw('coalesce(sum(p.qty_picking), 0) as total_picking')
            ->selectRaw(self::SUDAH_DIAMBIL.' as done_parts')
            ->selectRaw('exists(select 1 from public.bundlingh3 b where b.fk_do = p.fk_do) as is_bundling')
            ->groupBy('p.fk_do')
            ->orderByRaw('max(p.tgl_picking_list_part) desc');

        $this->areaOperator->saring($query, $this->areaOperator->areaUntuk($user), 'p.lokasi_part');
        $this->saringAreaChannel($query, $saring['area'] ?? null);
        $this->saringPencarian($query, $saring['cari'] ?? null);
        $this->saringWaktu($query, $status, $saring['tgl_dari'] ?? null, $saring['tgl_sampai'] ?? null);
        $this->saringStatusDo($query, $status);

        return $query->paginate(self::PER_HALAMAN)
            ->withQueryString()
            ->through(fn (object $baris): array => [
                'fk_do' => $baris->fk_do,
                'tgl_picking_list_part' => $baris->tgl_picking_list_part,
                'no_picking_list_part' => $baris->no_picking_list_part,
                'nama_channel' => $baris->nama_channel ?: 'Channel '.$baris->fk_dealer,
                'fk_dealer' => $baris->fk_dealer,
                'area' => $baris->area,
                'total_items' => (int) $baris->total_items,
                'total_picking' => (int) $baris->total_picking,
                'done_parts' => (int) $baris->done_parts,
                'status_do' => $this->statusDo((int) $baris->done_parts, (int) $baris->total_items),
                'is_bundling' => (bool) $baris->is_bundling,
            ]);
    }

    /**
     * Area penjualan yang benar-benar dipakai DO, bukan seluruh isi master
     * channel.
     */
    public function daftarAreaChannel(): array
    {
        return AreaChannel::query()
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->whereIn('kode_channel', PickingInoma::query()
                ->whereNotNull('fk_dealer')
                ->where('fk_dealer', '!=', '')
                ->distinct()
                ->select('fk_dealer'))
            ->distinct()
            ->orderBy('area')
            ->pluck('area')
            ->all();
    }

    public function hapusDo(string $fkDo): int
    {
        return PickingInoma::query()->where('fk_do', $fkDo)->delete();
    }

    private function statusDo(int $selesai, int $total): string
    {
        if ($selesai === 0) {
            return 'Waiting';
        }

        return $selesai === $total ? 'Done' : 'On Progress';
    }

    private function saringAreaChannel(Builder $query, ?string $area): void
    {
        if ($area) {
            $query->where('c.area', $area);
        }
    }

    private function saringPencarian(Builder $query, ?string $kunci): void
    {
        if (! $kunci) {
            return;
        }

        $pola = '%'.$kunci.'%';

        $query->where(fn (Builder $bagian) => $bagian
            ->where('p.fk_do', 'ilike', $pola)
            ->orWhere('p.fk_dealer', 'ilike', $pola)
            ->orWhere('c.nama_channel', 'ilike', $pola)
            ->orWhere('c.area', 'ilike', $pola));
    }

    /**
     * Batas waktu meniru aplikasi lama: status Done boleh dirunut lewat rentang
     * tanggal, "Semua Status" dikunci ke hari ini, sisanya tiga bulan terakhir.
     * Baris yang belum punya `waktu_done` dinilai dari tanggal picking list.
     */
    private function saringWaktu(Builder $query, string $status, ?string $dari, ?string $sampai): void
    {
        if ($status === 'Done') {
            if (! $dari && ! $sampai) {
                $dari = $sampai = now()->toDateString();
            }

            $query->where(function (Builder $bagian) use ($dari, $sampai): void {
                $bagian->where(fn (Builder $q) => $this->antara($q, 'p.waktu_done', $dari, $sampai, true))
                    ->orWhere(fn (Builder $q) => $q
                        ->whereNull('p.waktu_done')
                        ->where(fn (Builder $q2) => $this->antara($q2, 'p.tgl_picking_list_part', $dari, $sampai, false)));
            });

            return;
        }

        if ($status === 'all') {
            $query->whereDate('p.tgl_picking_list_part', now()->toDateString());

            return;
        }

        $query->where('p.tgl_picking_list_part', '>=', now()->subMonths(3));
    }

    private function antara(Builder $query, string $kolom, ?string $dari, ?string $sampai, bool $pakaiJam): Builder
    {
        if ($dari) {
            $query->where($kolom, '>=', $pakaiJam ? $dari.' 00:00:00' : $dari);
        }

        if ($sampai) {
            $query->where($kolom, '<=', $pakaiJam ? $sampai.' 23:59:59' : $sampai);
        }

        return $query;
    }

    private function saringStatusDo(Builder $query, string $status): void
    {
        match ($status) {
            'Done' => $query->havingRaw(self::SUDAH_DIAMBIL.' = count(*)'),
            'Waiting' => $query->havingRaw(self::SUDAH_DIAMBIL.' = 0'),
            'On Progress' => $query->havingRaw(self::SUDAH_DIAMBIL.' > 0')
                ->havingRaw(self::SUDAH_DIAMBIL.' < count(*)'),
            self::STATUS_BAWAAN => $query->havingRaw(self::SUDAH_DIAMBIL.' < count(*)'),
            default => $query,
        };
    }
}
