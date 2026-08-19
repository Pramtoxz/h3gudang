<?php

namespace App\Services\Picking;

use App\Models\AdminUser;
use App\Models\H3\AreaChannel;
use App\Models\H3\KartuStock;
use App\Models\H3\PickingInoma;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    /**
     * Hapus satu baris item dari DO, apa pun statusnya. Meniru `deleteDOItemByAdmin()`.
     */
    public function hapusItem(int $id): int
    {
        return PickingInoma::query()->where('id', $id)->delete();
    }

    /**
     * Semua part dalam satu DO, meniru `getDetailData()` aplikasi lama:
     * join `tbl_area_channel` untuk nama channel dan `public.tblpart` untuk
     * nama part. Urutan lama: part number lalu lokasi rak.
     */
    public function daftarPartDalamDo(AdminUser $user, string $fkDo): array
    {
        $query = DB::connection('pgsql_dms')
            ->table('H3.tbl_picking_inoma as p')
            ->leftJoin('H3.tbl_area_channel as c', 'p.fk_dealer', '=', 'c.kode_channel')
            ->leftJoin('public.tblpart as t', 'p.fk_part', '=', 't.kd_part')
            ->select(
                'p.id',
                'p.fk_do',
                'p.fk_dealer',
                'p.tgl_picking_list_part',
                'p.no_picking_list_part',
                'p.fk_part',
                't.nm_part',
                'p.lokasi_part',
                'p.qty_part',
                'p.qty_picking',
                'p.status_picking_list',
                'p.waktu_done',
                'p.keterangan_picking',
                'c.nama_channel',
                'c.area',
            )
            ->where('p.fk_do', $fkDo);

        $this->areaOperator->saring($query, $this->areaOperator->areaUntuk($user), 'p.lokasi_part');

        return $query
            ->orderBy('p.lokasi_part')
            ->orderBy('p.fk_part')
            ->get()
            ->map(fn (object $baris): array => [
                'id' => (int) $baris->id,
                'fk_do' => $baris->fk_do,
                'fk_dealer' => $baris->fk_dealer,
                'tgl_picking_list_part' => $baris->tgl_picking_list_part,
                'fk_part' => strtoupper((string) $baris->fk_part),
                'nm_part' => $baris->nm_part ?: '-',
                'lokasi_part' => strtoupper((string) $baris->lokasi_part),
                'keterangan_picking' => $baris->keterangan_picking ?: '-',
                'nama_channel' => $baris->nama_channel ?: 'Channel '.$baris->fk_dealer,
                'area' => $baris->area ?: '-',
                'qty_part' => (int) $baris->qty_part,
                'qty_picking' => (int) $baris->qty_picking,
                'status_picking_list' => $baris->status_picking_list,
                'waktu_done' => $baris->waktu_done,
            ])
            ->all();
    }

    /**
     * Apakah DO ini bagian dari bundling (badge URGENT). Meniru subquery
     * `EXISTS public.bundlingh3` milik aplikasi lama.
     */
    public function doBundling(string $fkDo): bool
    {
        return DB::connection('pgsql_dms')
            ->table('public.bundlingh3')
            ->where('fk_do', $fkDo)
            ->exists();
    }

    /**
     * Meniru `updateStatus()` aplikasi lama persis:
     *
     * - **Done**: `status_picking_list = 'done'`, `waktu_done = now()`, dan
     *   `qty_picking` disamakan dengan `qty_part`. Mengembalikan daftar
     *   `kartustok_list` supaya frontend membuka modal input Kartu Stok.
     * - **Undo** (dari `done`): membuat baris `H3.kartustok` ber-`fk_do`
     *   `{do}-UNDO` (barang masuk kembali ke rak), lalu status kembali
     *   `Ready For Scan`, `waktu_done` dan `qty_picking` dinolkan.
     *
     * Item berstatus `final` dikunci — tidak bisa done maupun undo.
     */
    public function updateStatusPart(int $id, string $status): array
    {
        $item = PickingInoma::query()->find($id);

        abort_unless((bool) $item, 404, 'Data tidak ditemukan.');

        if ($item->status_picking_list === PickingInoma::STATUS_FINAL) {
            return [
                'success' => false,
                'message' => 'Item sudah Final Check dan tidak bisa diubah dari sini.',
            ];
        }

        $kartustokList = [];

        DB::connection('pgsql_dms')->transaction(function () use ($item, $status, &$kartustokList): void {
            if ($status === 'done') {
                $item->waktu_done = now();
                $item->status_picking_list = PickingInoma::STATUS_DONE;
                $item->qty_picking = $item->qty_part;

                $kartustokList[] = [
                    'fk_do' => (string) $item->fk_do,
                    'fk_dealer' => (string) $item->fk_dealer,
                    'fk_part' => (string) $item->fk_part,
                    'lokasi_part' => (string) $item->lokasi_part,
                    'qty_part' => (int) $item->qty_part,
                ];
            } else {
                if ($item->status_picking_list === PickingInoma::STATUS_DONE) {
                    KartuStock::query()->create([
                        'fk_do' => $item->fk_do.'-UNDO',
                        'fk_dealer' => $item->fk_dealer,
                        'tgl_kartu' => now()->toDateString(),
                        'no_part' => $item->fk_part,
                        'kode_rak' => $item->lokasi_part,
                        'qty_masuk' => $item->qty_part,
                        'qty_keluar' => null,
                        'status_masuk' => true,
                    ]);
                }

                $item->waktu_done = null;
                $item->status_picking_list = PickingInoma::STATUS_SIAP;
                $item->qty_picking = 0;
            }

            $item->save();
        });

        return [
            'success' => true,
            'message' => 'Status berhasil di update',
            'waktu_done' => $item->waktu_done?->toDateTimeString(),
            'kartustok_list' => $kartustokList,
        ];
    }

    /**
     * Menyimpan input Kartu Stok keluar. Aturan aplikasi lama yang dipertahankan:
     *
     * - `tgl_kartu = CURRENT_DATE`, `qty_masuk = null`, `status_masuk = false`.
     * - **Validasi ketat**: `jumlah_input` harus sama persis dengan `qty_part`
     *   pada baris picking — operator menghitung buta tanpa melihat Qty Part.
     *   Bila beda, seluruh batch ditolak (transaksi).
     *
     * @param  array<int, array{fk_do: string, fk_dealer: string, fk_part: string, lokasi_part: string, jumlah_input: int}>  $items
     */
    public function simpanKartuStokKeluar(array $items): int
    {
        // Validasi qty harus persis sebelum menulis apa pun.
        foreach ($items as $item) {
            $baris = PickingInoma::query()
                ->where('fk_do', $item['fk_do'])
                ->where('fk_part', $item['fk_part'])
                ->where('lokasi_part', $item['lokasi_part'])
                ->first();

            if (! $baris) {
                throw ValidationException::withMessages([
                    'items' => 'Part '.$item['fk_part'].' tidak ditemukan pada DO '.$item['fk_do'].'.',
                ]);
            }

            if ((int) $item['jumlah_input'] !== (int) $baris->qty_part) {
                throw ValidationException::withMessages([
                    'items' => 'Jumlah barang yang keluar tidak sesuai dengan Qty Part DO. Jika ragu, hitung ulang part yang Anda keluarkan atau hubungi kepala gudang segera.',
                ]);
            }
        }

        $now = now();

        $barisInsert = array_map(fn (array $item): array => [
            'fk_do' => $item['fk_do'],
            'fk_dealer' => $item['fk_dealer'],
            'tgl_kartu' => now()->toDateString(),
            'no_part' => $item['fk_part'],
            'kode_rak' => $item['lokasi_part'],
            'qty_masuk' => null,
            'qty_keluar' => (int) $item['jumlah_input'],
            'status_masuk' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ], $items);

        DB::connection('pgsql_dms')->transaction(function () use ($barisInsert): void {
            DB::connection('pgsql_dms')->table('H3.kartustok')->insert($barisInsert);
        });

        return count($barisInsert);
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
