<?php

namespace App\Console\Commands;

use App\Jobs\RefreshCollectionCache;
use App\Repositories\CollectionRepository;
use Illuminate\Console\Command;

class RefreshCollectionCacheCommand extends Command
{
    protected $signature = 'collection:refresh-cache';

    protected $description = 'Menyegarkan cache piutang seluruh toko aktif dari DMS';

    public function handle(CollectionRepository $repository): int
    {
        $this->info('Refresh cache piutang dimulai...');

        $mulai = now();

        try {
            (new RefreshCollectionCache)->handle($repository);
        } catch (\Throwable $e) {
            $this->error('Gagal: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('Selesai dalam ' . (int) abs($mulai->diffInSeconds(now())) . ' detik.');

        return self::SUCCESS;
    }
}
