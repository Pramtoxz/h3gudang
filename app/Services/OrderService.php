<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\DataPart\SalesOrder;
use App\Models\DataPart\SalesOrderDetail;
use App\Models\MSendHO;
use App\Models\PublicSchema\Part;
use App\Models\Serial;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OrderService
{
    private const KATEGORI_OLI = 'OIL';

    private const ID_DEADLINE = 2;

    private const ID_KONFIG_WA_GRUP = 2;

    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function submitOrder(int $userId): array
    {
        return DB::transaction(function () use ($userId): array {
            $keranjang = $this->ambilKeranjangSiapCheckout($userId);

            $this->pastikanBelumLewatDeadline();

            $jenisOrder = $this->tentukanJenisOrder($keranjang);
            $grandTotal = $keranjang->items->sum('subtotal');
            $noSo = Serial::generateSO();

            $this->simpanSalesOrder($noSo, $jenisOrder, $grandTotal, $keranjang);

            $this->kirimNotifikasi($keranjang, $noSo, $grandTotal, $userId);

            $keranjang->items()->delete();
            $keranjang->delete();

            return [
                'no_so' => $noSo,
                'jenis_so' => $jenisOrder,
                'grand_total' => $grandTotal,
                'status' => 'Waiting For Approval',
            ];
        });
    }

    private function ambilKeranjangSiapCheckout(int $userId): Cart
    {
        $keranjang = Cart::where('user_id', $userId)
            ->where('status', 'active')
            ->with(['items.part', 'user.toko'])
            ->first();

        if (! $keranjang) {
            throw new RuntimeException('Keranjang belanja kosong atau sudah di-checkout');
        }

        if ($keranjang->items->isEmpty()) {
            throw new RuntimeException('Keranjang belanja kosong');
        }

        return $keranjang;
    }

    private function pastikanBelumLewatDeadline(): void
    {
        $deadline = MSendHO::find(self::ID_DEADLINE);

        if (! $deadline) {
            return;
        }

        $batas = Carbon::parse($deadline->tgl_kirim_akhir->format('Y-m-d') . ' ' . $deadline->jam);

        if (now()->greaterThan($batas)) {
            throw new RuntimeException('Checkout ditutup sementara. Silakan tunggu periode selanjutnya.');
        }
    }

    private function simpanSalesOrder(string $noSo, string $jenisOrder, float|int|string $grandTotal, Cart $keranjang): void
    {
        SalesOrder::create([
            'no_so' => $noSo,
            'jenis_so' => $jenisOrder,
            'tgl_so' => now(),
            'jenis_pembayaran' => 'Cash',
            'fk_salesman' => $keranjang->user->toko->fk_sales ?? null,
            'tipe_source' => 'OTHER',
            'fk_toko' => $keranjang->user->fk_toko,
            'tipe_penjualan' => 'Reguler',
            'tgl_jatuh_tempo' => now()->addMonth(),
            'grand_total' => $grandTotal,
            'status_outstanding' => true,
            'status_approve_reject' => 'Waiting For Approval',
            'keterangan' => 'Order by PMO',
        ]);

        foreach ($keranjang->items as $item) {
            SalesOrderDetail::create([
                'fk_so' => $noSo,
                'fk_part' => $item->kode_part,
                'harga' => $item->harga,
                'qty_so' => $item->qty,
                'total_harga' => $item->subtotal,
                'qty_sisa' => $item->qty,
                'fk_tipe' => '',
            ]);
        }
    }

    /**
     * Sales Order sudah tersimpan di DMS yang berada di luar transaksi ini,
     * sehingga kegagalan notifikasi tidak boleh membatalkan checkout.
     */
    private function kirimNotifikasi(Cart $keranjang, string $noSo, float|int|string $grandTotal, int $userId): void
    {
        try {
            $this->kirimNotifikasiGrupWhatsApp($keranjang, $noSo, $grandTotal);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi WA order: ' . $e->getMessage(), ['no_so' => $noSo]);
        }

        try {
            $this->notificationService->kirimNotifikasiPesanan($userId, $noSo, 'created');
        } catch (\Throwable $e) {
            Log::error('Gagal kirim push notification order: ' . $e->getMessage(), ['no_so' => $noSo]);
        }
    }

    private function kirimNotifikasiGrupWhatsApp(Cart $keranjang, string $noSo, float|int|string $grandTotal): void
    {
        $pesan = "🔔 *ORDER BARU - PMO*\n\n"
            . "No. SO: *{$noSo}*\n"
            . 'Toko: *' . $keranjang->user->toko->nama . "*\n"
            . 'Kode Toko: ' . $keranjang->user->fk_toko . "\n"
            . 'Jumlah Item: ' . $keranjang->items->count() . "\n"
            . 'Total: *Rp ' . number_format((float) $grandTotal, 0, ',', '.') . "*\n\n"
            . 'Waktu Order ' . now()->format('d/m/Y H:i:s');

        (new WhatsAppGateway(self::ID_KONFIG_WA_GRUP))->sendToGroup($pesan);
    }

    private function tentukanJenisOrder(Cart $keranjang): string
    {
        $jumlahOli = 0;
        $jumlahPart = 0;

        $kategoriPerPart = Part::whereIn('kd_part', $keranjang->items->pluck('kode_part'))
            ->pluck('fk_detail_sub_kelompok_part', 'kd_part');

        foreach ($keranjang->items as $item) {
            $kategoriPerPart->get($item->kode_part) === self::KATEGORI_OLI
                ? $jumlahOli++
                : $jumlahPart++;
        }

        if ($jumlahPart !== $jumlahOli) {
            return $jumlahPart < $jumlahOli ? 'Oli Regular' : 'Other';
        }

        $partPertama = $keranjang->items->first()?->kode_part;

        return $kategoriPerPart->get($partPertama) === self::KATEGORI_OLI ? 'Oli Regular' : 'Other';
    }
}
