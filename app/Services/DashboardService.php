<?php

namespace App\Services;

use App\Models\CollectionCacheStatus;
use App\Models\User;

class DashboardService
{
    public function statistik(User $user): array
    {
        return [
            'deliveryProgress' => '0%',
            'monthlyBuyIn' => 'Rp 0',
            'cartCount' => $user->activeCart()->first()?->totalItems ?? 0,
            'collectionLastUpdate' => $this->pembaruanTagihanTerakhir(),
        ];
    }

    private function pembaruanTagihanTerakhir(): ?array
    {
        $terakhir = CollectionCacheStatus::where('status', 'success')
            ->orderBy('last_refresh_at', 'desc')
            ->first();

        if (! $terakhir) {
            return null;
        }

        return [
            'last_refresh_at' => $terakhir->last_refresh_at,
            'total_shops_processed' => $terakhir->total_shops_processed,
            'total_records' => $terakhir->total_records,
            'duration_seconds' => $terakhir->duration_seconds,
        ];
    }
}
