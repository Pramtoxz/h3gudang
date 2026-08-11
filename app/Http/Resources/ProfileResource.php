<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $toko = $this->toko;
        $sales = $toko?->sales;
        $nomorSales = $sales?->no_hp;

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'dealerCode' => $toko?->kode,
            'dealerName' => $toko?->nama,
            'salesName' => $sales?->nama,
            'salesPhone' => $nomorSales,
            'salesWhatsapp' => $nomorSales
                ? 'https://wa.me/' . preg_replace('/\D/', '', $nomorSales)
                : null,
            'phone' => $toko?->no_hp,
            'address' => $toko?->alamat,
            'npwp' => $toko?->npwp,
            'city' => $toko?->kota,
            'province' => $toko?->provinsi,
        ];
    }
}
