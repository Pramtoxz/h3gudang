<?php

namespace App\Http\Resources;

use App\Helpers\PartHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PublicSchema\Part
 */
class PartDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stok = $this->stock->first();
        $discontinued = ! $this->part_active;

        return [
            'id' => (string) $this->kd_part,
            'image' => PartHelper::getPartImage($this->kd_part, $this->product, $this->resource),
            'partNumber' => $this->kd_part,
            'name' => PartHelper::getPartName($this->resource, $this->product),
            'description' => PartHelper::getPartDescription($this->resource, $this->product),
            'price' => (float) $this->het,
            'isReady' => $stok?->is_available ?? false,
            'stock' => $stok ? max(0, $stok->available) : 0,
            'category' => $this->fk_detail_sub_kelompok_part,
            'isDiscontinued' => $discontinued,
            'canOrder' => ! $discontinued,
            'discontinuedMessage' => $discontinued
                ? 'Part ini sudah tidak diproduksi (discontinued). Hanya untuk referensi harga.'
                : null,
        ];
    }
}
