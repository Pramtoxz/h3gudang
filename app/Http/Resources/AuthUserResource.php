<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $toko = $this->toko;

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'dealerCode' => $toko?->kode,
            'dealerName' => $toko?->nama,
            'phone' => $toko?->no_hp,
            'address' => $toko?->alamat,
            'city' => $toko?->kota,
            'province' => $toko?->provinsi,
        ];
    }
}
