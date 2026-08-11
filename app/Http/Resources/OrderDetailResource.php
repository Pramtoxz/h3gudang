<?php

namespace App\Http\Resources;

use App\Helpers\PartHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DataPart\SalesOrder
 */
class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalQtyOrder = $this->details->sum('qty_so');
        $totalQtySisa = $this->details->sum('qty_sisa');

        return [
            'orderNumber' => $this->no_so,
            'orderType' => $this->jenis_so,
            'orderDate' => $this->tgl_so->format('Y-m-d H:i:s'),
            'grandTotal' => (float) $this->grand_total,
            'status' => $this->status_approve_reject,
            'summary' => [
                'totalItems' => $this->details->count(),
                'totalQtyOrder' => $totalQtyOrder,
                'totalQtyDelivered' => $totalQtyOrder - $totalQtySisa,
                'totalQtyBackOrder' => $totalQtySisa,
            ],
            'items' => $this->details->map(fn ($detail): array => [
                'partNumber' => $detail->fk_part,
                'partName' => PartHelper::getPartName($detail->part),
                'image' => PartHelper::getPartImage($detail->fk_part, null, $detail->part),
                'orderQty' => $detail->qty_so,
                'deliveryQty' => $detail->qty_so - $detail->qty_sisa,
                'backOrderQty' => $detail->qty_sisa,
                'price' => (float) $detail->harga,
                'subtotal' => (float) $detail->total_harga,
            ]),
            'deliveryOrders' => $this->deliveryOrders->map(fn ($do): array => [
                'noDo' => $do->no_do,
                'tanggal' => $do->tgl_do->format('Y-m-d H:i:s'),
                'status' => $do->status_approve_reject,
                'grandTotal' => (float) $do->grand_total,
                'items' => $do->details->map(fn ($detail): array => [
                    'partNumber' => $detail->fk_part,
                    'partName' => PartHelper::getPartName($detail->part),
                    'qtyDo' => $detail->qty_do,
                    'price' => (float) $detail->harga,
                    'diskon' => (float) $detail->diskon,
                    'subtotal' => (float) $detail->total_harga,
                ]),
            ]),
        ];
    }
}
