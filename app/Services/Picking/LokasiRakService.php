<?php

namespace App\Services\Picking;

use App\Models\H3\GudangPart;
use App\Models\H3\LokasiRak;
use App\Support\Picking\AreaRak;
use Illuminate\Support\Facades\DB;

class LokasiRakService
{
    private const TABEL = 'H3.tbllokasi_part';

    private const TABEL_GUDANG = 'H3.tblgudang_part';

    /**
     * Lokasi ber-area GUDANG OLI dipaksa bernama gudang "GUDANG OLI" meski
     * tersimpan di gudang lain. Ini menyatukan GDG-1 dan GDG-2 jadi satu baris
     * dan wajib dipertahankan — tanpanya jumlah kelompok berubah dari 19 jadi
     * 20 dan tidak lagi cocok dengan aplikasi lama.
     */
    private function namaGudangSql(string $ekspresiArea): string
    {
        return "CASE WHEN ({$ekspresiArea}) = 'GUDANG OLI' THEN 'GUDANG OLI' ELSE btrim(g.nm_gudang_part) END";
    }

    public function ringkasanArea(): array
    {
        $area = AreaRak::ekspresiSql('l.kd_lokasi');
        $namaGudang = $this->namaGudangSql($area);

        return DB::connection('pgsql_dms')
            ->table(self::TABEL.' as l')
            ->join(self::TABEL_GUDANG.' as g', 'g.kd_gudang_part', '=', 'l.fk_gudang_part')
            ->selectRaw($area.' as area_rak')
            ->selectRaw($namaGudang.' as nama_gudang')
            ->selectRaw('count(*) as total_lokasi')
            ->selectRaw('count(*) filter (where l.lokasi_part_active) as total_aktif')
            ->selectRaw("string_agg(distinct l.fk_gudang_part, ',') as kode_gudang")
            ->groupByRaw($area.', '.$namaGudang)
            ->orderByRaw('1, 2')
            ->get()
            ->map(fn (object $baris): array => [
                'area_rak' => $baris->area_rak,
                'nama_gudang' => $baris->nama_gudang,
                'kode_gudang' => explode(',', $baris->kode_gudang),
                'total_lokasi' => (int) $baris->total_lokasi,
                'total_aktif' => (int) $baris->total_aktif,
            ])
            ->all();
    }

    /**
     * @param  list<string>  $kodeGudang
     */
    public function detailLokasi(string $area, array $kodeGudang): array
    {
        return $this->queryKelompok($area, $kodeGudang)
            ->orderBy('kd_lokasi')
            ->get(['kd_lokasi', 'fk_gudang_part', 'jenis_lokasi', 'kapasitas', 'lokasi_part_active'])
            ->map(fn (LokasiRak $lokasi): array => [
                'kd_lokasi' => $lokasi->kd_lokasi,
                'fk_gudang_part' => $lokasi->fk_gudang_part,
                'jenis_lokasi' => $lokasi->jenis_lokasi,
                'kapasitas' => $lokasi->kapasitas,
                'lokasi_part_active' => $lokasi->lokasi_part_active,
            ])
            ->all();
    }

    public function daftarGudang(): array
    {
        return GudangPart::query()
            ->orderBy('nm_gudang_part')
            ->get(['kd_gudang_part', 'nm_gudang_part', 'gudang_part_active'])
            ->map(fn (GudangPart $gudang): array => [
                'kode' => $gudang->kd_gudang_part,
                'nama' => trim((string) $gudang->nm_gudang_part),
                'aktif' => $gudang->gudang_part_active,
            ])
            ->all();
    }

    public function daftarJenisLokasi(): array
    {
        return LokasiRak::query()
            ->whereNotNull('jenis_lokasi')
            ->where('jenis_lokasi', '!=', '')
            ->distinct()
            ->orderBy('jenis_lokasi')
            ->pluck('jenis_lokasi')
            ->all();
    }

    public function simpan(array $data): LokasiRak
    {
        return LokasiRak::create($this->siapkan($data));
    }

    public function perbarui(LokasiRak $lokasi, array $data): void
    {
        $lokasi->update($this->siapkan(collect($data)->except('kd_lokasi')->all()));
    }

    public function hapus(LokasiRak $lokasi): void
    {
        $lokasi->delete();
    }

    /**
     * @param  list<string>  $kodeGudang
     */
    public function hapusMassal(string $area, array $kodeGudang): int
    {
        return $this->queryKelompok($area, $kodeGudang)->delete();
    }

    /**
     * @param  list<string>  $kodeGudang
     */
    public function jumlahDiArea(string $area, array $kodeGudang): int
    {
        return $this->queryKelompok($area, $kodeGudang)->count();
    }

    /**
     * @param  list<string>  $kodeGudang
     */
    private function queryKelompok(string $area, array $kodeGudang)
    {
        return LokasiRak::query()
            ->whereIn('fk_gudang_part', $kodeGudang)
            ->whereRaw(AreaRak::ekspresiSql('kd_lokasi').' = ?', [$area]);
    }

    /**
     * Kolom `kapasitas` bertipe text, jadi angkanya harus dikirim sebagai
     * string agar PostgreSQL tidak menerima parameter bertipe integer.
     */
    private function siapkan(array $data): array
    {
        if (array_key_exists('kapasitas', $data)) {
            $data['kapasitas'] = (string) $data['kapasitas'];
        }

        return $data;
    }
}
