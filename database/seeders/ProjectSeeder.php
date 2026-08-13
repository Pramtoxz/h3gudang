<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    private const PROJECT = [
        ['pmo', 'PMO', 'Pemesanan sparepart oleh toko', 'ShoppingCart', 1, true],
        ['picking', 'Picking', 'Picking & final check gudang part', 'PackageCheck', 2, true],
    ];

    public function run(): void
    {
        foreach (self::PROJECT as [$kode, $nama, $keterangan, $ikon, $urutan, $aktif]) {
            Project::query()->updateOrCreate(
                ['kode' => $kode],
                [
                    'nama' => $nama,
                    'keterangan' => $keterangan,
                    'ikon' => $ikon,
                    'urutan' => $urutan,
                    'aktif' => $aktif,
                ],
            );
        }
    }
}
