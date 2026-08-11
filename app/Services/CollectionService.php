<?php

namespace App\Services;

use App\Models\CollectionCache;
use App\Models\DataFA\Invoice;
use App\Repositories\CollectionRepository;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class CollectionService
{
    private const CACHE_TTL = 300;

    private const BATAS_LUNAS = 100;

    public function __construct(private readonly CollectionRepository $repository)
    {
    }

    public function daftar(string $kdToko, array $filter): array
    {
        $halaman = max(1, (int) ($filter['page'] ?? 1));
        $perHalaman = (int) ($filter['per_page'] ?? 50);

        $ringkasan = $this->ringkasanDariCache($kdToko);

        $outstanding = CollectionCache::where('kd_toko', $kdToko)
            ->where('status', 'Outstanding')
            ->orderBy('tgl_faktur', 'desc')
            ->offset(($halaman - 1) * $perHalaman)
            ->limit($perHalaman)
            ->get();

        [$lunas, $ringkasanLunas, $pakaiCache] = $this->ambilLunas(
            $kdToko,
            $filter['dari'] ?? null,
            $filter['sampai'] ?? null,
            $ringkasan
        );

        return [
            'summary' => [
                'totalTagihan' => (float) ($ringkasan['total_nilai'] ?? 0),
                'totalTerbayar' => (float) ($ringkasanLunas['paid_nilai'] ?? 0),
                'totalOutstanding' => (float) ($ringkasan['total_saldo'] ?? 0),
                'jumlahInvoice' => ($ringkasan['total_count'] ?? 0) + ($ringkasanLunas['paid_count'] ?? 0),
                'jumlahOutstanding' => (int) ($ringkasan['total_count'] ?? 0),
                'jumlahPaid' => (int) ($ringkasanLunas['paid_count'] ?? 0),
                'outstandingDisplayed' => $outstanding->count(),
                'currentPage' => $halaman,
                'perPage' => $perHalaman,
                'hasMore' => $outstanding->count() >= $perHalaman,
                'cached' => $pakaiCache,
            ],
            'outstanding' => $outstanding->map(fn ($item): array => $this->formatItem($item))->values(),
            'paid' => $lunas->map(fn ($item): array => $this->formatItem($item))->values(),
        ];
    }

    public function ringkasan(string $kdToko): array
    {
        $tagihan = $this->repository->ambilTagihan($kdToko)['collections'];

        return [
            'totalTagihan' => (float) $tagihan->sum('nilai_faktur'),
            'totalTerbayar' => (float) $tagihan->where('saldo', 0)->sum('nilai_faktur'),
            'totalOutstanding' => (float) $tagihan->sum('saldo'),
            'jumlahInvoice' => $tagihan->count(),
            'jumlahOutstanding' => $tagihan->where('saldo', '>', 0)->count(),
            'jumlahPaid' => $tagihan->where('saldo', 0)->count(),
        ];
    }

    public function pengingat(string $kdToko): array
    {
        return $this->repository->ambilTagihan($kdToko)['collections']
            ->where('saldo', '>', 0)
            ->map(fn ($item): array => [
                'noFaktur' => $item->no_faktur,
                'tanggal' => $this->tanggal($item->tgl_faktur, 'Y-m-d'),
                'jatuhTempo' => null,
                'sisaHari' => null,
                'saldo' => (float) $item->saldo,
                'message' => 'Tagihan belum dibayar',
            ])
            ->values()
            ->all();
    }

    public function detailFaktur(string $kdToko, string $noFaktur): array
    {
        $faktur = Invoice::with(['deliveryOrder.salesOrder', 'accountReceivable'])
            ->where('no_faktur', $noFaktur)
            ->first();

        if (! $faktur) {
            throw new RuntimeException('Invoice not found', 404);
        }

        $deliveryOrder = $faktur->deliveryOrder;

        if (! $deliveryOrder) {
            throw new RuntimeException('Delivery order not found', 404);
        }

        $salesOrder = $deliveryOrder->salesOrder;

        if (! $salesOrder || $salesOrder->fk_toko !== $kdToko) {
            throw new RuntimeException('Unauthorized access to invoice', 403);
        }

        $item = $this->repository->ambilItemFaktur($faktur->fk_do_part);

        $nilaiGross = $item->sum(fn ($baris): float => (float) $baris->harga * $baris->qty_do);
        $totalDiskon = $item->sum(fn ($baris): float => (float) $baris->diskon * $baris->qty_do);
        $nilaiNett = $nilaiGross - $totalDiskon;
        $saldo = $faktur->accountReceivable->saldo ?? null;

        return [
            'noFaktur' => $faktur->no_faktur,
            'tanggal' => $this->tanggal($faktur->tgl_faktur, 'Y-m-d H:i:s'),
            'noDo' => $faktur->fk_do_part,
            'noSo' => $salesOrder->no_so,
            'jenisPembayaran' => $salesOrder->jenis_pembayaran ?? 'Unknown',
            'nilaiGross' => (float) $nilaiGross,
            'totalDiskon' => (float) $totalDiskon,
            'nilaiNett' => (float) $nilaiNett,
            'saldo' => (float) ($saldo ?? $nilaiNett),
            'status' => $saldo !== null && $saldo == 0 ? 'Paid' : 'Outstanding',
            'items' => $item->map(fn ($baris): array => [
                'partCode' => $baris->fk_part,
                'partName' => $baris->part_name,
                'qty' => $baris->qty_do,
                'harga' => (float) $baris->harga,
                'diskon' => (float) $baris->diskon,
                'subtotal' => (float) $baris->subtotal,
            ])->values(),
        ];
    }

    private function ringkasanDariCache(string $kdToko): array
    {
        return Cache::remember(
            sprintf('collections_summary_%s_%s_%s', $kdToko, date('n'), date('Y')),
            self::CACHE_TTL,
            function () use ($kdToko): array {
                $outstanding = CollectionCache::where('kd_toko', $kdToko)->where('status', 'Outstanding');
                $lunas = CollectionCache::where('kd_toko', $kdToko)->where('status', 'Paid');

                return [
                    'total_count' => (clone $outstanding)->count(),
                    'total_saldo' => (clone $outstanding)->sum('saldo'),
                    'total_nilai' => $outstanding->sum('nilai_faktur'),
                    'paid_count' => (clone $lunas)->count(),
                    'paid_nilai' => $lunas->sum('nilai_faktur'),
                ];
            }
        );
    }

    /**
     * Data lunas 30 hari terakhir sudah tersedia di tabel cache, di luar rentang itu
     * harus diambil langsung dari DMS.
     */
    private function ambilLunas(string $kdToko, ?string $dari, ?string $sampai, array $ringkasan): array
    {
        if (! $dari || ! $sampai) {
            return [new EloquentCollection, ['paid_count' => 0, 'paid_nilai' => 0], false];
        }

        $rentangCache = $dari === now()->subDays(30)->format('Y-m-d') && $sampai === now()->format('Y-m-d');

        if ($rentangCache) {
            $lunas = CollectionCache::where('kd_toko', $kdToko)
                ->where('status', 'Paid')
                ->orderBy('tgl_faktur', 'desc')
                ->limit(self::BATAS_LUNAS)
                ->get();

            return [
                $lunas,
                ['paid_count' => $ringkasan['paid_count'] ?? 0, 'paid_nilai' => $ringkasan['paid_nilai'] ?? 0],
                true,
            ];
        }

        $lunas = $this->repository->ambilTagihanRentangTanggal($kdToko, $dari, $sampai)
            ->where('status', 'Paid');

        return [
            $lunas,
            ['paid_count' => $lunas->count(), 'paid_nilai' => $lunas->sum('nilai_faktur')],
            false,
        ];
    }

    private function formatItem(object $item): array
    {
        return [
            'noFaktur' => $item->no_faktur,
            'tanggal' => $this->tanggal($item->tgl_faktur, 'Y-m-d H:i:s'),
            'noDo' => $item->fk_do_part,
            'noSo' => $item->no_so,
            'nilaiFaktur' => (float) $item->nilai_faktur,
            'saldo' => (float) $item->saldo,
            'status' => $item->status,
            'jenisPembayaran' => $item->jenis_pembayaran,
        ];
    }

    private function tanggal(mixed $nilai, string $format): string
    {
        return is_string($nilai) ? $nilai : $nilai->format($format);
    }
}
