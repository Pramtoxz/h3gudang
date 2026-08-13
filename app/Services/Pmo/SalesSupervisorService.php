<?php

namespace App\Services\Pmo;

use App\Models\SalesSupervisor;
use App\Models\Toko;

class SalesSupervisorService
{
    public function daftar(): array
    {
        return SalesSupervisor::query()
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode_npk', 'jabatan', 'no_hp', 'aktif'])
            ->map(fn (SalesSupervisor $item): array => [
                'id' => $item->id,
                'nama' => $item->nama,
                'kode_npk' => $item->kode_npk,
                'jabatan' => $item->jabatan,
                'no_hp' => $item->no_hp,
                'aktif' => (bool) $item->aktif,
            ])
            ->all();
    }

    public function detail(SalesSupervisor $salesSupervisor): array
    {
        return [
            'salesSupervisor' => [
                'id' => $salesSupervisor->id,
                'nama' => $salesSupervisor->nama,
                'kode_npk' => $salesSupervisor->kode_npk,
                'jabatan' => $salesSupervisor->jabatan,
                'no_hp' => $salesSupervisor->no_hp,
                'aktif' => (bool) $salesSupervisor->aktif,
            ],
            'daftarToko' => $this->tokoDipegang($salesSupervisor),
        ];
    }

    public function hapus(SalesSupervisor $salesSupervisor): void
    {
        $salesSupervisor->delete();
    }

    private function tokoDipegang(SalesSupervisor $salesSupervisor): array
    {
        $sebagaiSales = $salesSupervisor->tokoAsSales()
            ->orderBy('toko')
            ->get(['kd_toko', 'toko'])
            ->map(fn (Toko $toko): array => $this->baris($toko, 'salesman'));

        $sebagaiSupervisor = $salesSupervisor->tokoAsSupervisor()
            ->orderBy('toko')
            ->get(['kd_toko', 'toko'])
            ->map(fn (Toko $toko): array => $this->baris($toko, 'supervisor'));

        return $sebagaiSales->concat($sebagaiSupervisor)->all();
    }

    private function baris(Toko $toko, string $peran): array
    {
        return [
            'kd_toko' => $toko->kd_toko,
            'toko' => $toko->toko,
            'peran' => $peran,
        ];
    }
}
