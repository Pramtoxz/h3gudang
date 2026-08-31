<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Picking\PickingPartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API endpoint untuk kerja satu DO di layar lapangan.
 * 
 * - GET /api/lapangan/do/{fkDo}/parts → semua part dalam DO (urut rak)
 * - POST /api/lapangan/part/update-status → mark done / undo
 * - POST /api/lapangan/kartustok → simpan kartu stok keluar
 */
class LapangWorkController extends Controller
{
    public function __construct(
        private readonly PickingPartService $service,
    ) {
    }

    /**
     * GET /api/lapangan/do/{fkDo}/parts
     * 
     * Daftar semua part dalam satu DO, disaring ke area operator,
     * urut: waiting dulu → lokasi rak → part number.
     */
    public function parts(Request $request, string $fkDo): JsonResponse
    {
        $user = $request->user();
        
        $daftarPart = $this->service->daftarPartDalamDo($user, $fkDo);

        if ($daftarPart === []) {
            return response()->json([
                'success' => false,
                'message' => 'DO tidak ditemukan atau tidak ada part di area Anda.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $daftarPart,
            'is_bundling' => $this->service->doBundling($fkDo),
        ]);
    }

    /**
     * POST /api/lapangan/part/update-status
     * 
     * Body: { id: int, status: 'done' | 'waiting' }
     * 
     * Mark done → set status='done', waktu_done=now, qty_picking=qty_part.
     * Undo → kembali ke 'Ready For Scan' + insert baris UNDO di kartustok.
     * 
     * Return `kartustok_list` bila ada input kartu stok yang perlu dilakukan.
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'status' => 'required|in:done,waiting',
        ]);

        $result = $this->service->updateStatusPart(
            $request->user(),
            (int) $validated['id'],
            $validated['status']
        );

        return response()->json($result);
    }

    /**
     * POST /api/lapangan/kartustok
     * 
     * Body: {
     *   items: [{
     *     fk_do: string,
     *     fk_dealer: string,
     *     fk_part: string,
     *     lokasi_part: string,
     *     jumlah_input: int
     *   }]
     * }
     * 
     * Simpan kartu stok keluar untuk satu DO (batch). Validasi ketat:
     * jumlah_input harus persis sama dengan qty_part — operator menghitung
     * buta tanpa melihat Qty Part.
     */
    public function simpanKartuStok(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.fk_do' => 'required|string',
            'items.*.fk_dealer' => 'required|string',
            'items.*.fk_part' => 'required|string',
            'items.*.lokasi_part' => 'required|string',
            'items.*.jumlah_input' => 'required|integer|min:1',
        ]);

        try {
            $count = $this->service->simpanKartuStokKeluar($request->user(), $validated['items']);

            return response()->json([
                'success' => true,
                'message' => "Kartu Stok berhasil disimpan ({$count} item).",
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan Kartu Stok: ' . $e->getMessage(),
            ], 500);
        }
    }
}
