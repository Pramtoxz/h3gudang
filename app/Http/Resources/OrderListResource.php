<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DataPart\SalesOrder
 */
class OrderListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalQtyOrder = $this->details->sum('qty_so');
        $totalQtySisa = $this->details->sum('qty_sisa');

        return [
            'id' => $this->no_so,
            'orderNumber' => $this->no_so,
            'orderType' => $this->jenis_so,
            'orderDate' => $this->tgl_so->format('Y-m-d H:i:s'),
            'grandTotal' => (float) $this->grand_total,
            'status' => $this->status_approve_reject,
            'fulfillment' => [
                'totalQtyOrder' => $totalQtyOrder,
                'totalQtyDelivered' => $totalQtyOrder - $totalQtySisa,
                'totalQtyBackOrder' => $totalQtySisa,
                'isCompleted' => $totalQtySisa == 0,
            ],
        ];
    }
}
