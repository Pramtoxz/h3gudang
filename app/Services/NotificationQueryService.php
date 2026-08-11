<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationQueryService
{
    private const PER_HALAMAN = 20;

    public function daftar(User $user): LengthAwarePaginator
    {
        return Notification::where('kd_toko', $user->fk_toko)
            ->orderBy('created_at', 'desc')
            ->paginate(self::PER_HALAMAN);
    }

    public function jumlahBelumDibaca(User $user): int
    {
        return Notification::where('kd_toko', $user->fk_toko)
            ->where('sudah_dibaca', false)
            ->count();
    }

    public function tandaiDibaca(User $user, int|string $id): Notification
    {
        $notifikasi = Notification::where('id', $id)
            ->where('kd_toko', $user->fk_toko)
            ->firstOrFail();

        $notifikasi->sudah_dibaca = true;
        $notifikasi->save();

        return $notifikasi;
    }

    public function tandaiSemuaDibaca(User $user): void
    {
        Notification::where('kd_toko', $user->fk_toko)
            ->where('sudah_dibaca', false)
            ->update(['sudah_dibaca' => true]);
    }
}
