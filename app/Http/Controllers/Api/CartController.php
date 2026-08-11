<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddToCartRequest;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Http\Resources\CartItemResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $keranjang = $this->cartService->keranjangAktif($request->user());

        if (! $keranjang) {
            return ApiResponse::success([
                'items' => [],
                'summary' => ['totalItems' => 0, 'totalPrice' => 0],
            ]);
        }

        return ApiResponse::success([
            'items' => CartItemResource::collection($keranjang->items),
            'summary' => [
                'totalItems' => $keranjang->totalItems,
                'totalPrice' => (float) $keranjang->total,
            ],
        ]);
    }

    public function add(AddToCartRequest $request): JsonResponse
    {
        try {
            $hasil = $this->cartService->tambahItem(
                $request->user(),
                $request->validated('partNumber'),
                $request->validated('quantity')
            );
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }

        return ApiResponse::success($hasil, $hasil['message']);
    }

    public function update(UpdateCartRequest $request, string $id): JsonResponse
    {
        $this->cartService->ubahJumlah($request->user(), $id, $request->validated('quantity'));

        return ApiResponse::success(null, 'Cart updated');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->cartService->hapusItem($request->user(), $id);

        return ApiResponse::success(null, 'Item removed from cart');
    }

    public function clear(Request $request): JsonResponse
    {
        $this->cartService->kosongkan($request->user());

        return ApiResponse::success(null, 'Cart cleared');
    }
}
