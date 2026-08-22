<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Picking\AreaOperatorService;
use App\Services\Picking\PickingPartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API daftar DO untuk layar lapangan. Penyaringnya dibuat identik dengan
 * `Picking\PickingPartController@index` supaya angka di HP tidak pernah
 * berbeda dari angka di web untuk penyaring yang sama.
 */
class LapangDoController extends Controller
{
    public function __construct(
        private readonly PickingPartService $service,
        private readonly AreaOperatorService $areaOperator,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $saring = $this->saringDari($request);
        $paginator = $this->service->daftarDo($user, $saring);

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
            'daftar_area_channel' => $paginator->currentPage() === 1
                ? $this->service->daftarAreaChannel()
                : [],
        ]);
    }

    private function saringDari(Request $request): array
    {
        return [
            'area' => $request->query('area') ?: null,
            'status' => $request->query('status') ?: null,
            'tgl_dari' => $request->query('tgl_dari') ?: null,
            'tgl_sampai' => $request->query('tgl_sampai') ?: null,
            'cari' => $request->query('cari') ?: null,
        ];
    }
}
