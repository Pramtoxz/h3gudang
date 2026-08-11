<?php

namespace App\Http\Resources;

use App\Helpers\PartHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PublicSchema\Part
 */
class PartListResource extends JsonResource
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
            'category' => $this->fk_detail_sub_kelompok_part,
            'isReady' => $stok?->is_available ?? false,
            'isDiscontinued' => $discontinued,
            'canOrder' => ! $discontinued,
        ];
    }
}
