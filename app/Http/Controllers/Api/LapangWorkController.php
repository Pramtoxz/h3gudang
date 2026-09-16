<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Picking\PickingPartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class LapangWorkController extends Controller
{
    public function __construct(
        private readonly PickingPartService $service,
    ) {}

    public function parts(Request $request, string $fkDo): JsonResponse
    {
        $user = $request->user();
        $fkDo = urldecode($fkDo);

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


    public function updateStatus(Request $request): JsonResponse
    {
        if (!$request->has('id') && $request->has('item_id')) {
            $request->merge(['id' => $request->input('item_id')]);
        }

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
