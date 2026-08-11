<?php

namespace App\Jobs;

use App\Models\CollectionCache;
use App\Models\CollectionCacheStatus;
use App\Models\Toko;
use App\Repositories\CollectionRepository;
use App\Services\WhatsAppGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshCollectionCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    private const ID_KONFIG_WA_GRUP = 2;

    private const UKURAN_CHUNK = 100;

    private const BATAS_HARI_LUNAS = 30;

    public function handle(CollectionRepository $repository): void
    {
        $mulai = now();
        $bulan = (int) date('n');
        $tahun = (int) date('Y');

        CollectionCacheStatus::create(['status' => 'running', 'last_refresh_at' => $mulai]);

        $this->kabari("[PMO] Refresh cache piutang dimulai pada {$mulai->format('d/m/Y H:i:s')}.\nProses berlangsung ±40 menit.");

        try {
            $kodeToko = Toko::where('toko_active', true)->pluck('kd_toko');
            $totalToko = $kodeToko->count();
            $berhasil = 0;
            $gagal = 0;

            foreach ($kodeToko as $kode) {
                try {
                    $this->segarkanToko($repository, $kode, $bulan, $tahun);
                    $berhasil++;
                } catch (\Throwable $e) {
                    $gagal++;
                    Log::error("RefreshCollectionCache: gagal toko {$kode}", ['error' => $e->getMessage()]);
                }
            }

            $selesai = now();
            $durasi = (int) abs($mulai->diffInSeconds($selesai));
            $totalData = CollectionCache::where('bulan', $bulan)->where('tahun', $tahun)->count();

            CollectionCacheStatus::create([
                'status' => 'success',
                'last_refresh_at' => $selesai,
                'total_shops_processed' => $berhasil,
                'total_records' => $totalData,
                'duration_seconds' => $durasi,
            ]);

            $this->kabari(
                "[PMO] Refresh cache piutang SELESAI.\nToko: {$berhasil}/{$totalToko} | Gagal: {$gagal} | Data: {$totalData} | Durasi: {$durasi} detik."
            );
        } catch (\Throwable $e) {
            Log::error('RefreshCollectionCache: fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            CollectionCacheStatus::create([
                'status' => 'failed',
                'last_refresh_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            $this->kabari("[PMO] Refresh cache piutang GAGAL.\nError: " . $e->getMessage());

            throw $e;
        }
    }

    private function segarkanToko(CollectionRepository $repository, string $kdToko, int $bulan, int $tahun): void
    {
        CollectionCache::where('kd_toko', $kdToko)->delete();

        $baris = [];

        $outstanding = $repository->ambilTagihan($kdToko, 1, 9999)['collections']
            ->where('status', 'Outstanding');

        foreach ($outstanding as $item) {
            $baris[] = $this->barisCache($kdToko, $item, 'Outstanding', $item->saldo ?? 0, $bulan, $tahun);
        }

        $lunas = $repository->ambilTagihanLunas(
            $kdToko,
            now()->subDays(self::BATAS_HARI_LUNAS)->format('Y-m-d'),
            now()->format('Y-m-d'),
            9999
        );

        foreach ($lunas as $item) {
            $baris[] = $this->barisCache($kdToko, $item, 'Paid', 0, $bulan, $tahun);
        }

        foreach (array_chunk($baris, self::UKURAN_CHUNK) as $bagian) {
            CollectionCache::insert($bagian);
        }
    }

    private function barisCache(string $kdToko, object $item, string $status, mixed $saldo, int $bulan, int $tahun): array
    {
        return [
            'kd_toko' => $kdToko,
            'tgl_faktur' => is_string($item->tgl_faktur) ? $item->tgl_faktur : $item->tgl_faktur->format('Y-m-d'),
            'jenis_pembayaran' => $item->jenis_pembayaran ?? null,
            'no_faktur' => $item->no_faktur,
            'fk_do_part' => $item->fk_do_part ?? null,
            'no_so' => $item->no_so ?? null,
            'nilai_faktur' => $item->nilai_faktur ?? 0,
            'saldo' => $saldo,
            'status' => $status,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'cached_at' => now(),
        ];
    }

    private function kabari(string $pesan): void
    {
        try {
            (new WhatsAppGateway(self::ID_KONFIG_WA_GRUP))->sendToGroup($pesan);
        } catch (\Throwable $e) {
            Log::warning('RefreshCollectionCache: notifikasi WA gagal', ['error' => $e->getMessage()]);
        }
    }
}
