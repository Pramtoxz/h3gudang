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
        $stockSummary = $this->getStockSummary();
        $discontinued = ! $this->part_active;

        $marketName = $this->nama_pasar ?? $this->product?->nama ?? $this->nm_part ?? '-';
        $partDescription = PartHelper::getPartDescription($this->resource, $this->product);

        return [
            'id' => (string) $this->kd_part,
            'image' => PartHelper::getPartImage($this->kd_part, $this->product, $this->resource),
            'partNumber' => $this->kd_part,
            'name' => PartHelper::getPartName($this->resource, $this->product),
            'description' => $partDescription,
            'partDescription' => $partDescription,
            'nama_pasar' => $marketName,
            'namaPasar' => $marketName,
            'marketName' => $marketName,
            'price' => (float) $this->het,
            'het' => (float) $this->het,
            'discount' => 0.15,
            'avg_order' => (int) ($this->avg_order ?? 12),
            'avgOrder' => (int) ($this->avg_order ?? 12),
            'category' => $this->fk_detail_sub_kelompok_part,
            'isReady' => $stockSummary->is_ready,
            'isDiscontinued' => $discontinued,
            'canOrder' => ! $discontinued,
        ];
    }
}
