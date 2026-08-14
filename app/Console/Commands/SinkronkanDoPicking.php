<?php

namespace App\Console\Commands;

use App\Services\Picking\SinkronisasiDoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SinkronkanDoPicking extends Command
{
    protected $signature = 'picking:sync-do';

    protected $description = 'Menarik picking list Ready For Scan dari DMS ke H3.tbl_picking_inoma';

    public function handle(SinkronisasiDoService $sinkronisasi): int
    {
        $mulai = microtime(true);

        try {
            $hasil = $sinkronisasi->jalankan();
        } catch (\Throwable $e) {
            Log::error('Sinkronisasi DO picking gagal: '.$e->getMessage());
            $this->error('Gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        $detik = round(microtime(true) - $mulai, 2);

        $this->info(sprintf(
            'Sinkronisasi DO selesai dalam %ss — dibaca %d, dilewati %d, disimpan %d.',
            $detik,
            $hasil['dibaca'],
            $hasil['dilewati'],
            $hasil['disimpan'],
        ));

        return self::SUCCESS;
    }
}
