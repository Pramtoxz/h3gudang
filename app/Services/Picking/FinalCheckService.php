<?php

namespace App\Services\Picking;

use App\Models\AdminUser;
use App\Models\H3\PickingInoma;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Final Check bersifat **terpusat, tanpa penyaringan area rak** — itu perilaku
 * `picking-old` yang berjalan di produksi, dan sudah dikonfirmasi sebagai
 * spesifikasi (md/08-PICKING.md §8 keputusan 1). Berbeda dengan Picking Part
 * yang menyaring baris ke area jatah operator.
 */
class FinalCheckService
{
    public const PER_HALAMAN = 25;

    public const STATUS_BAWAAN = 'Done';

    private const SUDAH_DIAMBIL = "count(*) filter (where p.status_picking_list in ('done','final'))";

    private const JUMLAH_FINAL = "count(*) filter (where p.status_picking_list = 'final')";

    public function __construct(private readonly WhatsAppFinalCheck $notifikasi)
    {
    }

    /**
     * Aplikasi lama menarik seluruh baris ke PHP, mengelompokkannya, lalu
     * menjalankan **dua query tambahan di dalam loop** untuk setiap DO demi
     * menentukan statusnya. Di sini pengelompokan, penentuan status, dan
     * paginasi seluruhnya dikerjakan database.
     *
     * @param  array{area?: ?string, status?: ?string, tgl_dari?: ?string, tgl_sampai?: ?string, cari?: ?string}  $saring
     */
    public function daftarDo(array $saring): LengthAwarePaginator
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
            ->selectRaw('max(p.poli) as poli')
            ->selectRaw('max(p.tgl_final) as tgl_final')
            ->selectRaw('count(*) as total_items')
            ->selectRaw(self::JUMLAH_FINAL.' as final_parts')
            ->selectRaw('exists(select 1 from public.bundlingh3 b where b.fk_do = p.fk_do) as is_bundling')
            ->groupBy('p.fk_do')
            ->orderByRaw('max(p.tgl_picking_list_part) desc');

        $this->saringAreaChannel($query, $saring['area'] ?? null);
        $this->saringPencarian($query, $saring['cari'] ?? null);
        $this->saringStatus($query, $status, $saring['tgl_dari'] ?? null, $saring['tgl_sampai'] ?? null);

        return $query->paginate(self::PER_HALAMAN)
            ->withQueryString()
            ->through(fn (object $baris): array => [
                'fk_do' => $baris->fk_do,
                'tgl_picking_list_part' => $baris->tgl_picking_list_part,
                'no_picking_list_part' => $baris->no_picking_list_part,
                'nama_channel' => $baris->nama_channel ?: 'Channel '.$baris->fk_dealer,
                'fk_dealer' => $baris->fk_dealer,
                'area' => $baris->area,
                'poli' => (int) $baris->poli,
                'tgl_final' => $baris->tgl_final,
                'total_items' => (int) $baris->total_items,
                'final_parts' => (int) $baris->final_parts,
                'status_do' => ((int) $baris->final_parts) > 0 ? 'Final' : 'Done',
                'is_bundling' => (bool) $baris->is_bundling,
            ]);
    }

    /**
     * Seluruh part dalam satu DO yang sudah diambil operator. Urutannya lokasi
     * rak menaik, sama seperti aplikasi lama, supaya petugas final check
     * menyusuri kotak dengan urutan yang sama tiap hari.
     *
     * @return array<int, array<string, mixed>>
     */
    public function daftarPartDalamDo(string $fkDo): array
    {
        return DB::connection('pgsql_dms')
            ->table('H3.tbl_picking_inoma as p')
            ->leftJoin('H3.tbl_area_channel as c', 'p.fk_dealer', '=', 'c.kode_channel')
            ->leftJoin('public.tblpart as t', 'p.fk_part', '=', 't.kd_part')
            ->select(
                'p.id',
                'p.fk_do',
                'p.fk_dealer',
                'p.fk_part',
                't.nm_part',
                'p.lokasi_part',
                'p.qty_part',
                'p.qty_picking',
                'p.nomor_kotak',
                'p.user_cek',
                'p.tgl_final',
                'p.status_picking_list',
                'p.keterangan_final',
                'p.keterangan_picking',
                'p.poli',
                'p.tgl_picking_list_part',
                'c.nama_channel',
                'c.area',
            )
            ->where('p.fk_do', $fkDo)
            ->whereIn('p.status_picking_list', [PickingInoma::STATUS_DONE, PickingInoma::STATUS_FINAL])
            ->orderBy('p.lokasi_part')
            ->orderBy('p.fk_part')
            ->get()
            ->map(fn (object $baris): array => [
                'id' => (int) $baris->id,
                'fk_part' => strtoupper((string) $baris->fk_part),
                'nm_part' => $baris->nm_part ?: '-',
                'lokasi_part' => strtoupper((string) $baris->lokasi_part),
                'qty_part' => (int) $baris->qty_part,
                'qty_picking' => (int) $baris->qty_picking,
                'nomor_kotak' => $baris->nomor_kotak,
                'user_cek' => $baris->user_cek,
                'tgl_final' => $baris->tgl_final,
                'status_picking_list' => $baris->status_picking_list,
            ])
            ->all();
    }

    /**
     * Keterangan kepala halaman detail: channel, dealer, tanggal DO, dan
     * keterangan picking — persis empat butir yang ditampilkan aplikasi lama.
     *
     * @return array<string, mixed>|null
     */
    public function infoDo(string $fkDo): ?array
    {
        $baris = DB::connection('pgsql_dms')
            ->table('H3.tbl_picking_inoma as p')
            ->leftJoin('H3.tbl_area_channel as c', 'p.fk_dealer', '=', 'c.kode_channel')
            ->where('p.fk_do', $fkDo)
            ->whereIn('p.status_picking_list', [PickingInoma::STATUS_DONE, PickingInoma::STATUS_FINAL])
            ->orderBy('p.lokasi_part')
            ->first([
                'p.fk_dealer', 'p.keterangan_picking', 'p.keterangan_final', 'p.poli',
                'p.tgl_picking_list_part', 'c.nama_channel', 'c.area',
            ]);

        if (! $baris) {
            return null;
        }

        return [
            'nama_channel' => $baris->nama_channel ?: 'Channel '.$baris->fk_dealer,
            'fk_dealer' => $baris->fk_dealer,
            'area' => $baris->area ?: '-',
            'tgl_picking_list_part' => $baris->tgl_picking_list_part,
            'keterangan_picking' => $baris->keterangan_picking ?: '-',
            'keterangan_final' => $baris->keterangan_final,
            'poli' => (int) $baris->poli,
        ];
    }

    public function doBundling(string $fkDo): bool
    {
        return DB::connection('pgsql_dms')
            ->table('public.bundlingh3')
            ->where('fk_do', $fkDo)
            ->exists();
    }

    /**
     * Menyimpan nomor kotak per part beserta keterangan & jumlah koli milik DO,
     * lalu menaikkan seluruh part berstatus `done` menjadi `final`.
     *
     * Notifikasi WhatsApp untuk DO bundling sengaja dikirim **di luar
     * transaksi** dan dibungkus try/catch: gateway yang sedang mati tidak boleh
     * membatalkan finalisasi yang sudah benar tersimpan.
     *
     * @param  array{keterangan_final?: ?string, poli?: ?int, kotak?: array<int, array{id: int, nomor_kotak: string}>}  $data
     * @return array{jumlah_final: int, jumlah_kotak: int}
     */
    public function simpanDanFinalkan(AdminUser $user, string $fkDo, array $data): array
    {
        $petugas = $user->name ?: $user->email;
        $sekarang = now();

        $hasil = DB::connection('pgsql_dms')->transaction(function () use ($fkDo, $data, $petugas, $sekarang): array {
            $milikDo = PickingInoma::query()->where('fk_do', $fkDo);

            $infoDo = array_filter([
                'keterangan_final' => $data['keterangan_final'] ?? null,
                'poli' => $data['poli'] ?? null,
            ], fn ($nilai) => $nilai !== null);

            if ($infoDo !== []) {
                (clone $milikDo)->update($infoDo + ['updated_at' => $sekarang]);
            }

            $jumlahKotak = 0;

            foreach ($data['kotak'] ?? [] as $kotak) {
                $jumlahKotak += PickingInoma::query()
                    ->where('id', $kotak['id'])
                    ->where('fk_do', $fkDo)
                    ->update([
                        'nomor_kotak' => $kotak['nomor_kotak'],
                        'tgl_final' => $sekarang,
                        'user_cek' => $petugas,
                        'updated_at' => $sekarang,
                    ]);
            }

            $jumlahFinal = (clone $milikDo)
                ->where('status_picking_list', PickingInoma::STATUS_DONE)
                ->update([
                    'status_picking_list' => PickingInoma::STATUS_FINAL,
                    'tgl_final' => $sekarang,
                    'user_cek' => $petugas,
                    'updated_at' => $sekarang,
                ]);

            return ['jumlah_final' => $jumlahFinal, 'jumlah_kotak' => $jumlahKotak];
        });

        if ($hasil['jumlah_final'] > 0 && $this->doBundling($fkDo)) {
            try {
                $this->notifikasi->kirim($fkDo);
            } catch (\Throwable $galat) {
                Log::error('Notifikasi WhatsApp final check gagal untuk DO '.$fkDo.': '.$galat->getMessage());
            }
        }

        return $hasil;
    }

    /**
     * Area penjualan yang benar-benar dipakai DO, bukan seluruh isi master
     * channel — sama seperti penyaring di Picking Part.
     *
     * @return array<int, string>
     */
    public function daftarAreaChannel(): array
    {
        return DB::connection('pgsql_dms')
            ->table('H3.tbl_area_channel')
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->whereIn('kode_channel', function ($sub): void {
                $sub->select('fk_dealer')
                    ->from('H3.tbl_picking_inoma')
                    ->whereNotNull('fk_dealer')
                    ->where('fk_dealer', '!=', '')
                    ->distinct();
            })
            ->distinct()
            ->orderBy('area')
            ->pluck('area')
            ->all();
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
     * Yang muncul di Final Check hanya DO yang **seluruh part-nya sudah
     * diambil** — DO setengah jalan tidak boleh ikut, karena kotaknya belum
     * lengkap. "Done" berarti siap difinalisasi, "Final" berarti sudah selesai.
     *
     * Rentang tanggal hanya berlaku untuk status Final dan dinilai dari
     * `max(tgl_final)`; kosong berarti hari ini, sama seperti aplikasi lama.
     */
    private function saringStatus(Builder $query, string $status, ?string $dari, ?string $sampai): void
    {
        $query->havingRaw(self::SUDAH_DIAMBIL.' = count(*)');

        if ($status === 'Final') {
            $query->havingRaw(self::JUMLAH_FINAL.' > 0');

            if (! $dari && ! $sampai) {
                $dari = $sampai = now()->toDateString();
            }

            if ($dari) {
                $query->havingRaw('max(p.tgl_final) >= ?', [$dari.' 00:00:00']);
            }

            if ($sampai) {
                $query->havingRaw('max(p.tgl_final) <= ?', [$sampai.' 23:59:59']);
            }

            return;
        }

        $query->havingRaw(self::JUMLAH_FINAL.' = 0');
    }
}
