<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Http\Resources\BackOrderResource;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderListResource;
use App\Services\OrderQueryService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderQueryService $orderQueryService,
    ) {
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            $hasil = $this->orderService->submitOrder($request->user()->id);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }

        return ApiResponse::success($hasil, 'Order submitted successfully');
    }

    public function history(Request $request): JsonResponse
    {
        $pesanan = $this->orderQueryService->riwayat(
            $request->user(),
            $request->only(['dari', 'sampai', 'filter', 'limit'])
        );

        return ApiResponse::success([
            'items' => OrderListResource::collection($pesanan),
        ]);
    }

    public function detail(Request $request, string $noSo): JsonResponse
    {
        $pesanan = $this->orderQueryService->cariPesanan($noSo, [
            'details.part',
            'deliveryOrders.details.part',
        ]);

        if (! $this->orderQueryService->bolehAkses($request->user(), $pesanan)) {
            return ApiResponse::error('Unauthorized access to order', 403);
        }

        return ApiResponse::success(new OrderDetailResource($pesanan));
    }

    public function backOrder(Request $request, string $noSo): JsonResponse
    {
        $pesanan = $this->orderQueryService->cariPesanan($noSo, ['details.part']);

        if (! $this->orderQueryService->bolehAkses($request->user(), $pesanan)) {
            return ApiResponse::error('Unauthorized access to order', 403);
        }

        return ApiResponse::success(new BackOrderResource($pesanan));
    }
}
