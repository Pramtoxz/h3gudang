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
    public const GROUP_OIL = 'OIL';
    public const GROUP_GMO = 'GMO';
    public const GROUP_TIRE = 'Tire';
    public const GROUP_HGP = 'HGP';

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

            $groupedItems = [
                self::GROUP_OIL  => [],
                self::GROUP_GMO  => [],
                self::GROUP_TIRE => [],
                self::GROUP_HGP  => [],
            ];

            foreach ($keranjang->items as $item) {
                $fkDetail = $item->part?->fk_detail_sub_kelompok_part;
                if (! $fkDetail && $item->part === null) {
                    $part = Part::where('kd_part', $item->kode_part)->first();
                    $fkDetail = $part?->fk_detail_sub_kelompok_part;
                }
                $group = $this->getPartGroup($fkDetail);
                $groupedItems[$group][] = $item;
            }

            $orders = [];
            $totalAllGrandTotal = 0;
            $totalAllItemsCount = 0;

            foreach ($groupedItems as $groupName => $items) {
                if (empty($items)) {
                    continue;
                }

                $groupTotal = 0;
                foreach ($items as $item) {
                    $groupTotal += (float) $item->subtotal;
                }
                $totalAllGrandTotal += $groupTotal;
                $totalAllItemsCount += count($items);

                $noSo = Serial::generateSO();
                $jenisOrder = $this->getJenisSoByGroup($groupName);
                $keterangan = $this->getKeteranganByGroup($groupName);

                $this->simpanSalesOrderGroup($noSo, $jenisOrder, $keterangan, $groupTotal, $keranjang, $items);

                $orders[] = [
                    'kelompok' => $groupName,
                    'no_so' => $noSo,
                    'jenis_so' => $jenisOrder,
                    'keterangan' => $keterangan,
                    'grand_total' => $groupTotal,
                    'items_count' => count($items),
                ];
            }

            if (empty($orders)) {
                throw new RuntimeException('Tidak ada item valid untuk diproses.');
            }

            $this->kirimNotifikasi($keranjang, $orders, $totalAllItemsCount, $userId);

            $keranjang->items()->delete();
            $keranjang->delete();

            $firstOrder = $orders[0];

            return [
                'no_so' => $firstOrder['no_so'],
                'jenis_so' => count($orders) > 1 ? 'Multi SO' : $firstOrder['jenis_so'],
                'grand_total' => $totalAllGrandTotal,
                'status' => 'Waiting For Approval',
                'orders' => $orders,
            ];
        });
    }

    public function getPartGroup(?string $fkDetailSubKelompok): string
    {
        $code = strtoupper(trim((string) $fkDetailSubKelompok));
        if ($code === 'OIL') {
            return self::GROUP_OIL;
        }
        if ($code === 'GMO') {
            return self::GROUP_GMO;
        }
        if ($code === 'TIRE' || $code === 'TIRE1') {
            return self::GROUP_TIRE;
        }

        return self::GROUP_HGP;
    }

    public function getJenisSoByGroup(string $group): string
    {
        return $group === self::GROUP_OIL ? 'Oli Regular' : 'Other';
    }

    public function getKeteranganByGroup(string $group): string
    {
        return 'Order by PMO - ' . $group;
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

    private function simpanSalesOrderGroup(
        string $noSo,
        string $jenisOrder,
        string $keterangan,
        float|int|string $grandTotal,
        Cart $keranjang,
        array $items
    ): void {
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
            'keterangan' => $keterangan,
        ]);

        foreach ($items as $item) {
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

    private function kirimNotifikasi(Cart $keranjang, array $orders, int $totalItemCount, int $userId): void
    {
        try {
            $this->kirimNotifikasiGrupWhatsApp($keranjang, $orders, $totalItemCount);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi WA order: ' . $e->getMessage(), ['orders' => $orders]);
        }

        try {
            $allSoNumbers = array_column($orders, 'no_so');
            $soSummary = implode(', ', $allSoNumbers);
            $this->notificationService->kirimNotifikasiPesanan($userId, $soSummary, 'created');
        } catch (\Throwable $e) {
            Log::error('Gagal kirim push notification order: ' . $e->getMessage());
        }
    }

    private function kirimNotifikasiGrupWhatsApp(Cart $keranjang, array $orders, int $totalItemCount): void
    {
        $pesan = "🔔 *ORDER BARU - PMO*\n\n"
            . 'Toko: *' . ($keranjang->user->toko->nama ?? 'Toko') . "*\n"
            . 'Kode Toko: ' . ($keranjang->user->fk_toko ?? '-') . "\n"
            . "Total Item: {$totalItemCount} item\n\n"
            . "*No. Sales Order:*\n";

        foreach ($orders as $order) {
            $kelompok = $order['kelompok'];
            $noSo = $order['no_so'];
            $itemCount = $order['items_count'];
            $pesan .= "• [{$kelompok}] {$noSo} ({$itemCount} item)\n";
        }

        $pesan .= "\nWaktu Order: " . now()->format('d/m/Y H:i:s');

        (new WhatsAppGateway(self::ID_KONFIG_WA_GRUP))->sendToGroup($pesan);
    }
}