<?php

namespace App\Console\Commands;

use App\Services\Picking\SinkronisasiStoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SinkronkanStoringPicking extends Command
{
    protected $signature = 'picking:sync-storing {--hari=30 : Jumlah hari data ke belakang yang ditarik}';

    protected $description = 'Menarik data penerimaan part (storing RS) dari DMS ke H3.kartustok';

    public function handle(SinkronisasiStoringService $sinkronisasi): int
    {
        $mulai = microtime(true);
        $hari = (int) $this->option('hari');

        try {
            $hasil = $sinkronisasi->jalankan($hari);
        } catch (\Throwable $e) {
            Log::error('Sinkronisasi Storing gagal: '.$e->getMessage());
            $this->error('Gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        $detik = round(microtime(true) - $mulai, 2);

        $this->info(sprintf(
            'Sinkronisasi Storing selesai dalam %ss — dibaca %d, baru %d, diperbarui %d, dilewati %d.',
            $detik,
            $hasil['dibaca'],
            $hasil['disimpan'],
            $hasil['diperbarui'],
            $hasil['dilewati'],
        ));

        return self::SUCCESS;
    }
}
