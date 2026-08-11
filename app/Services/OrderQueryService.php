<?php

namespace App\Services;

use App\Models\DataPart\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class OrderQueryService
{
    private const LIMIT_DEFAULT = 20;

    private const STATUS_MENUNGGU = 'Waiting For Approval';

    private const STATUS_DISETUJUI = 'Approve';

    public function riwayat(User $user, array $filter): Collection
    {
        $query = SalesOrder::with('details');

        $this->batasiMilikUser($query, $user);

        $pakaiRentangTanggal = filled($filter['dari'] ?? null) && filled($filter['sampai'] ?? null);

        if ($pakaiRentangTanggal) {
            $query->whereBetween('tgl_so', [
                $filter['dari'] . ' 00:00:00',
                $filter['sampai'] . ' 23:59:59',
            ]);
        }

        $jenis = $filter['filter'] ?? null;

        if ($jenis === 'pending') {
            $query->where('status_approve_reject', self::STATUS_MENUNGGU);
        } elseif (in_array($jenis, ['completed', 'back_order'], true)) {
            $query->where('status_approve_reject', self::STATUS_DISETUJUI);
        }

        $query->orderBy('tgl_so', 'desc');

        if (! $pakaiRentangTanggal) {
            $query->limit((int) ($filter['limit'] ?? self::LIMIT_DEFAULT));
        }

        return $this->saringBerdasarkanPemenuhan($query->get(), $jenis);
    }

    public function cariPesanan(string $noSo, array $relasi = ['details']): SalesOrder
    {
        return SalesOrder::with($relasi)->where('no_so', $noSo)->firstOrFail();
    }

    public function bolehAkses(User $user, SalesOrder $pesanan): bool
    {
        if ($user->fk_toko) {
            return $pesanan->fk_toko === $user->fk_toko;
        }

        return $pesanan->fk_salesman === $user->id;
    }

    private function batasiMilikUser($query, User $user): void
    {
        $user->fk_toko
            ? $query->where('fk_toko', $user->fk_toko)
            : $query->where('fk_salesman', $user->id);
    }

    /**
     * Status "Approve" mencakup pesanan yang sudah lengkap maupun yang masih
     * punya sisa kirim, sehingga pemisahannya dihitung dari qty_sisa.
     */
    private function saringBerdasarkanPemenuhan(Collection $pesanan, ?string $jenis): Collection
    {
        if ($jenis === 'completed') {
            return $pesanan->filter(fn (SalesOrder $so): bool => $so->details->sum('qty_sisa') == 0)->values();
        }

        if ($jenis === 'back_order') {
            return $pesanan->filter(fn (SalesOrder $so): bool => $so->details->sum('qty_sisa') > 0)->values();
        }

        return $pesanan;
    }
}
