<?php

namespace App\Http\Resources;

use App\Helpers\PartHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\CartItem
 */
class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'partId' => (string) $this->kode_part,
            'partNumber' => $this->kode_part,
            'name' => PartHelper::getPartName($this->part, $this->product),
            'image' => PartHelper::getPartImage($this->kode_part, $this->product, $this->part),
            'price' => (float) $this->harga,
            'quantity' => $this->qty,
            'subtotal' => (float) $this->subtotal,
            'isReady' => $this->part?->stock->first()?->is_available ?? false,
        ];
    }
}
