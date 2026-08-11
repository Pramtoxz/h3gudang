<?php

namespace App\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Query tagihan mengambil data dari database DMS dan bersifat baca saja.
 */
class CollectionRepository
{
    private const KONEKSI = 'pgsql_dms';

    private const JENIS_SO = ['Other', 'Oli Regular'];

    private const KOLOM_RINGKAS = "
        a.tgl_faktur,
        e.jenis_pembayaran,
        a.no_faktur,
        a.fk_do_part,
        e.no_so,
        ROUND(SUM(b.qty_do * (b.harga - b.diskon))) as nilai_faktur,
        COALESCE(c.saldo, 0) as saldo
    ";

    private const GROUP_BY = [
        'a.tgl_faktur', 'a.no_faktur', 'a.fk_do_part', 'a.pk_id_dealer_part',
        'c.saldo', 'e.no_so', 'e.fk_toko', 'e.jenis_so', 'e.jenis_pembayaran',
    ];

    /**
     * @return array{collections: Collection, outstandingSummary: object|null}
     */
    public function ambilTagihan(string $kdToko, int $halaman = 1, int $perHalaman = 50): array
    {
        $bulan = (int) date('n');
        $tahun = (int) date('Y');

        $ringkasan = $this->queryDasar($kdToko)
            ->selectRaw('COUNT(DISTINCT a.no_faktur) as total_count, SUM(c.saldo) as total_saldo, SUM(c.jumlah_transaksi) as total_nilai')
            ->where('c.bulan', $bulan)
            ->where('c.tahun', $tahun)
            ->where('c.saldo', '>', 0)
            ->first();

        $outstanding = $this->queryDetail($kdToko)
            ->selectRaw(self::KOLOM_RINGKAS . ", 'Outstanding' as status")
            ->where('c.bulan', $bulan)
            ->where('c.tahun', $tahun)
            ->where('c.saldo', '>', 0)
            ->groupBy(self::GROUP_BY)
            ->orderBy('a.tgl_faktur', 'desc')
            ->offset(($halaman - 1) * $perHalaman)
            ->limit($perHalaman)
            ->get();

        return ['collections' => $outstanding, 'outstandingSummary' => $ringkasan];
    }

    public function ambilTagihanRentangTanggal(string $kdToko, string $dari, string $sampai): Collection
    {
        return $this->queryDetail($kdToko)
            ->selectRaw(self::KOLOM_RINGKAS . ", CASE WHEN COALESCE(c.saldo, 0) = 0 THEN 'Paid' ELSE 'Outstanding' END as status")
            ->whereDate('a.tgl_faktur', '>=', $dari)
            ->whereDate('a.tgl_faktur', '<=', $sampai)
            ->groupBy(self::GROUP_BY)
            ->orderBy('a.tgl_faktur', 'desc')
            ->get();
    }

    public function ambilTagihanLunas(string $kdToko, string $dari, string $sampai, int $batas = 100): Collection
    {
        return $this->queryDetail($kdToko)
            ->selectRaw(self::KOLOM_RINGKAS . ", 'Paid' as status")
            ->where('c.saldo', '=', 0)
            ->whereDate('a.tgl_faktur', '>=', $dari)
            ->whereDate('a.tgl_faktur', '<=', $sampai)
            ->groupBy(self::GROUP_BY)
            ->orderBy('a.tgl_faktur', 'desc')
            ->limit($batas)
            ->get();
    }

    public function ambilFaktur(string $noFaktur): ?object
    {
        return DB::connection(self::KONEKSI)
            ->table('data_fa.tblinvoice_dealer_part')
            ->where('no_faktur', $noFaktur)
            ->first();
    }

    public function ambilItemFaktur(string $noDo): Collection
    {
        return DB::connection(self::KONEKSI)
            ->table('data_part.tbldo_detail as a')
            ->leftJoin('public.tblpart as b', 'a.fk_part', '=', 'b.kd_part')
            ->select('a.fk_part', 'a.qty_do', 'a.harga', 'a.diskon')
            ->selectRaw("COALESCE(b.nm_part, '-') as part_name")
            ->selectRaw('(a.harga - a.diskon) * a.qty_do as subtotal')
            ->where('a.fk_do', $noDo)
            ->get();
    }

    private function queryDasar(string $kdToko): Builder
    {
        return DB::connection(self::KONEKSI)
            ->table('data_fa.tblinvoice_dealer_part as a')
            ->leftJoin('data_fa.tblar as c', 'c.no_transaksi', '=', 'a.no_faktur')
            ->leftJoin('data_part.tbldo as d', 'a.fk_do_part', '=', 'd.no_do')
            ->leftJoin('data_part.tblso as e', 'e.no_so', '=', 'd.fk_so')
            ->where('e.fk_toko', $kdToko)
            ->where('a.no_faktur', 'LIKE', '%FAK%')
            ->whereIn('e.jenis_so', self::JENIS_SO)
            ->whereNotNull('e.fk_toko');
    }

    private function queryDetail(string $kdToko): Builder
    {
        return $this->queryDasar($kdToko)
            ->leftJoin('data_fa.tblinvoice_dealer_part_detail as b', 'a.pk_id_dealer_part', '=', 'b.fk_invoice');
    }
}
