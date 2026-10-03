<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Picking\AreaOperatorService;
use App\Services\Picking\StoringPartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LapangStoringController extends Controller
{
    public function __construct(
        private readonly StoringPartService $service,
        private readonly AreaOperatorService $areaOperator,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $saring = $this->saringDari($request);
        $paginator = $this->service->daftarStoring($user, $saring);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
            'saring' => $saring,
            'area_operator' => $this->areaOperator->areaUntuk($user),
        ]);
    }

    public function parts(Request $request, string $noPenerimaan): JsonResponse
    {
        $user = $request->user();
        $noPenerimaan = urldecode($noPenerimaan);

        $detail = $this->service->detailStoring($user, $noPenerimaan);

        if ($detail['parts'] === []) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen storing tidak ditemukan atau tidak ada part di area Anda.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'dokumen' => $detail['dokumen'],
            'data' => $detail['parts'],
        ]);
    }

    public function simpan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fk_do' => 'required|string',
            'no_part' => 'required|string',
            'kode_rak' => 'required|string',
            'qty_masuk' => 'required|integer|min:0',
        ]);

        try {
            $kartu = $this->service->simpanMasukRak($request->user(), $validated);

            return response()->json([
                'success' => true,
                'message' => 'Part berhasil disimpan ke rak.',
                'data' => $kartu,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan part: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function tandaiSemua(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fk_do' => 'required|string',
        ]);

        try {
            $jumlah = $this->service->tandaiSemuaSelesai($request->user(), $validated['fk_do']);

            return response()->json([
                'success' => true,
                'message' => "{$jumlah} item part berhasil ditandai selesai.",
                'jumlah' => $jumlah,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function saringDari(Request $request): array
    {
        return [
            'area' => $request->query('area') ?: null,
            'status' => $request->query('status') ?: null,
            'tgl_dari' => $request->query('tgl_dari') ?: null,
            'tgl_sampai' => $request->query('tgl_sampai') ?: null,
            'cari' => $request->query('cari') ?: null,
            'gudang' => $request->query('gudang') ?: null,
        ];
    }
}
