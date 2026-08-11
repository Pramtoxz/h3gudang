<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Nama kolom di database berbahasa Indonesia, sedangkan kontrak API mobile
 * memakai bahasa Inggris.
 *
 * @mixin \App\Models\Notification
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop_code' => $this->kd_toko,
            'title' => $this->judul,
            'message' => $this->pesan,
            'type' => $this->tipe,
            'is_read' => (bool) $this->sudah_dibaca,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
