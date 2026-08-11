<?php

namespace App\Services\Admin;

use App\Models\Campaign;
use App\Models\CollectionCacheStatus;
use App\Models\PopularPart;
use App\Models\PublicSchema\Part;
use App\Models\Toko;

class DashboardService
{
    private const JAM_BATAS_REFRESH_BERJALAN = 2;

    public function statistik(): array
    {
        return [
            'totalToko' => Toko::count(),
            'totalParts' => Part::where('part_active', true)->count(),
            'popularParts' => PopularPart::count(),
            'activeCampaigns' => Campaign::where('status', 'active')->count(),
        ];
    }

    public function statusCacheCollection(): array
    {
        return [
            'isRefreshing' => $this->sedangRefresh(),
            'lastRefresh' => $this->refreshTerakhir(),
        ];
    }

    private function sedangRefresh(): bool
    {
        return CollectionCacheStatus::query()
            ->where('status', 'running')
            ->where('last_refresh_at', '>=', now()->subHours(self::JAM_BATAS_REFRESH_BERJALAN))
            ->exists();
    }

    private function refreshTerakhir(): ?array
    {
        $status = CollectionCacheStatus::query()
            ->where('status', 'success')
            ->orderByDesc('last_refresh_at')
            ->first();

        if (! $status) {
            return null;
        }

        return [
            'last_refresh_at' => $status->last_refresh_at?->toIso8601String(),
            'last_refresh_diff' => $status->last_refresh_at?->locale('id')->diffForHumans(),
            'total_shops_processed' => $status->total_shops_processed,
            'total_records' => $status->total_records,
            'duration_seconds' => $status->duration_seconds,
        ];
    }
}
