<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\FilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function __construct(private readonly FilterService $filterService)
    {
    }

    public function getVehicleTypes(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->filterService->tipeKendaraan($request->get('search'))
        );
    }

    public function getCategories(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->filterService->kategoriPart($request->get('search'))
        );
    }
}
