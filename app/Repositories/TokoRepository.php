<?php

namespace App\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class TokoRepository
{
    public const ROLE_DEALER = 'dealer';

    public function barisExportToko(): array
    {
        return DB::connection('pgsql_pmo')->select("
            SELECT
                t.toko AS nama_toko,
                COALESCE(t.fk_sales, '') AS salesman,
                COALESCE(t.fk_spv, '') AS spv,
                t.kd_toko AS kode,
                COALESCE(u.email, '') AS email,
                COALESCE(t.no_telp, '') AS nohp,
                CASE WHEN t.toko_active = true THEN 'AKTIF' ELSE 'NONAKTIF' END AS status
            FROM pmov2.tbltoko t
            LEFT JOIN pmov2.users u ON u.fk_toko = t.kd_toko AND u.role = ?
            ORDER BY t.toko
        ", [self::ROLE_DEALER]);
    }

    /**
     * PIN Collection berlaku per toko, sehingga seluruh user toko ikut
     * ditampilkan - bukan hanya yang berperan dealer.
     */
    public function daftarUserToko(string $kdToko): array
    {
        return $this->tabelUsers()
            ->where('fk_toko', $kdToko)
            ->orderBy('role')
            ->orderBy('email')
            ->get(['email', 'role', 'collection_pin'])
            ->map(fn (object $user): array => [
                'email' => $user->email,
                'role' => $user->role,
                'punya_pin' => ! empty($user->collection_pin),
            ])
            ->all();
    }

    public function jumlahUser(string $kdToko): int
    {
        return $this->tabelUsers()->where('fk_toko', $kdToko)->count();
    }

    public function tokoAda(string $kdToko): bool
    {
        return $this->tabelToko()->where('kd_toko', $kdToko)->exists();
    }

    public function simpanTokoDariImport(string $kdToko, array $data): void
    {
        $this->tabelToko()->updateOrInsert(['kd_toko' => $kdToko], $data);
    }

    public function emailSudahDipakai(string $email): bool
    {
        return $this->tabelUsers()->where('email', $email)->exists();
    }

    public function buatUserToko(string $email, array $data): void
    {
        $this->tabelUsers()->insert(array_merge($data, [
            'email' => $email,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    public function perbaruiUserToko(string $email, array $data): void
    {
        $this->tabelUsers()
            ->where('email', $email)
            ->update(array_merge($data, ['updated_at' => now()]));
    }

    private function tabelToko(): Builder
    {
        return DB::connection('pgsql_pmo')->table('pmov2.tbltoko');
    }

    private function tabelUsers(): Builder
    {
        return DB::connection('pgsql_pmo')->table('pmov2.users');
    }
}
