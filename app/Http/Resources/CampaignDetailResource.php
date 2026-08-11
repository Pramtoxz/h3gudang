<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Campaign
 */
class CampaignDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->judul,
            'badge' => $this->badge,
            'description' => $this->deskripsi,
            'image' => $this->gambar ? url('images/kampanye/' . $this->gambar) : null,
            'startDate' => $this->tanggal_mulai->format('Y-m-d'),
            'endDate' => $this->tanggal_selesai->format('Y-m-d'),
            'status' => $this->status,
            'fullDescription' => $this->deskripsi_lengkap,
            'partsIncluded' => $this->part_termasuk ?? [],
            'termsAndConditions' => $this->syarat_ketentuan,
            'rewards' => $this->hadiah ?? [],
        ];
    }
}
