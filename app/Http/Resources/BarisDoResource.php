<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk serialisasi baris DO dari API lapangan.
 */
class BarisDoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'fk_do' => (string) $this->fk_do,
            'tgl_picking_list_part' => $this->tgl_picking_list_part?->toDateString(),
            'no_picking_list_part' => (string) $this->no_picking_list_part ?? '-',
            'nama_channel' => (string) $this->nama_channel ?: 'Channel ' . ($this->fk_dealer ?? ''),
            'fk_dealer' => $this->fk_dealer,
            'area' => $this->area,
            'total_items' => (int) $this->total_items,
            'total_picking' => (int) $this->total_picking,
            'done_parts' => (int) $this->done_parts,
            'status_do' => $this->status_do,
            'is_bundling' => $this->is_bundling ?? false,
        ];
    }
}
