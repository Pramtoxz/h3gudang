<?php

namespace App\Http\Resources;

use App\Helpers\PartHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DataPart\SalesOrder
 */
class BackOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $itemBackOrder = $this->details->filter(fn ($detail): bool => $detail->qty_sisa > 0);

        return [
            'orderNumber' => $this->no_so,
            'orderDate' => $this->tgl_so->format('Y-m-d H:i:s'),
            'totalBackOrderQty' => $itemBackOrder->sum('qty_sisa'),
            'backOrderItems' => $itemBackOrder->map(fn ($detail): array => [
                'partNumber' => $detail->fk_part,
                'partName' => PartHelper::getPartName($detail->part),
                'image' => PartHelper::getPartImage($detail->fk_part, null, $detail->part),
                'orderQty' => $detail->qty_so,
                'deliveryQty' => $detail->qty_so - $detail->qty_sisa,
                'backOrderQty' => $detail->qty_sisa,
                'price' => (float) $detail->harga,
            ])->values(),
        ];
    }
}
