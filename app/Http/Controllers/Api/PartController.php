<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\PartDetailResource;
use App\Http\Resources\PartListResource;
use App\Services\PartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartController extends Controller
{
    private const LIMIT_DEFAULT = 20;

    private const LIMIT_MAKS = 50;

    public function __construct(private readonly PartService $partService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->get('page', 1));
        $limit = min((int) $request->get('limit', self::LIMIT_DEFAULT), self::LIMIT_MAKS);

        $hasil = $this->partService->daftar(
            $request->only(['vehicle_type', 'category', 'search']),
            $page,
            $limit
        );

        return ApiResponse::success([
            'items' => PartListResource::collection($hasil['items']),
            'pagination' => [
                'currentPage' => $page,
                'perPage' => $limit,
                'hasMore' => $hasil['hasMore'],
            ],
        ]);
    }

    public function show(string $partNumber): JsonResponse
    {
        return ApiResponse::success(
            new PartDetailResource($this->partService->detail($partNumber))
        );
    }

    public function checkStock(string $partNumber): JsonResponse
    {
        return ApiResponse::success($this->partService->cekStok($partNumber));
    }
}
