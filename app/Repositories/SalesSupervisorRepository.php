<?php

namespace App\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SalesSupervisorRepository
{
    /**
     * Email diambil lewat pencocokan nama karena tabel sales_supervisor tidak
     * punya kolom penghubung ke tabel user. Dipertahankan agar bentuk berkas
     * export tetap sama seperti @old.
     */
    public function barisExport(): array
    {
        return DB::connection('pgsql')->select("
            SELECT
                ss.nama,
                COALESCE(u.email, '') AS email,
                ss.jabatan,
                COALESCE(ss.no_hp, '') AS nohp,
                COALESCE(ss.kode_npk, '') AS kode
            FROM pmov2.sales_supervisor ss
            LEFT JOIN pmov2.users u ON u.id = (
                SELECT id FROM pmov2.users
                WHERE name = ss.nama
                  AND role IN ('sales', 'supervisor')
                LIMIT 1
            )
            WHERE ss.aktif = true
            ORDER BY ss.jabatan, ss.nama
        ");
    }

    public function simpanDariImport(string $kodeNpk, array $data): void
    {
        $this->tabel()->updateOrInsert(['kode_npk' => $kodeNpk], $data);
    }

    private function tabel(): Builder
    {
        return DB::connection('pgsql')->table('pmov2.sales_supervisor');
    }
}
