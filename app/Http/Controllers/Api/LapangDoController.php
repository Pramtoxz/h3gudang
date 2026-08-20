<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Picking\PickingPartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API endpoint untuk daftar DO yang ada part waiting di area operator.
 * 
 * Operator lapangan hanya melihat DO yang punya item menunggu di area
 * rak jatahnya (di-filter oleh `PickingPartService::daftarDo()`).
 */
class LapangDoController extends Controller
{
    public function __construct(
        private readonly PickingPartService $service,
    ) {
    }

    /**
     * GET /api/lapangan/do
     * 
     * Daftar DO dengan status "Waiting" atau "On Progress", disaring ke
     * area rak operator. Response:
     * 
     * {
     *   success: true,
     *   data: [...baris DO]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Filter status = 'Waiting' saja (belum ada satu item pun yang done)
        $paginator = $this->service->daftarDo($user, ['status' => 'Waiting']);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
