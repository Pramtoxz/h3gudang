<?php

namespace App\Services\Picking;

use App\Models\AdminUser;
use App\Models\H3\AksesArea;
use App\Models\MenuAkses;
use App\Support\Picking\AreaRak;

class AksesAreaService
{
    private const PANJANG_USERNAME = 50;

    /**
     * `ada_di_dms` ikut dikirim karena sebagian baris memakai email yang tidak
     * terdaftar di `public.users`, sehingga pemiliknya tidak bisa login.
     */
    public function daftar(): array
    {
        $baris = AksesArea::query()->orderBy('email')->get();

        $terdaftar = AdminUser::query()
            ->whereIn('email', $baris->pluck('email')->all())
            ->pluck('name', 'email');

        return $baris
            ->map(fn (AksesArea $akses): array => [
                'email' => $akses->email,
                'username' => $akses->username,
                'area' => $akses->area,
                'level' => $akses->level,
                'nama_dms' => $terdaftar->get($akses->email),
                'ada_di_dms' => $terdaftar->has($akses->email),
            ])
            ->all();
    }

    /**
     * Calon penerima hanya email yang sudah diberi menu oleh IT di Kelola Hak
     * Akses. Pengelola IT sendiri tidak ikut ditampilkan, dan seluruh isi
     * `public.users` sengaja tidak dibuka di halaman ini.
     */
    public function daftarUserTerdaftar(): array
    {
        return $this->queryUserTerdaftar()
            ->orderBy('name')
            ->get(['name', 'email'])
            ->map(fn (AdminUser $user): array => [
                'nama' => $user->name,
                'email' => $user->email,
            ])
            ->all();
    }

    public function emailTerdaftar(): array
    {
        return $this->queryUserTerdaftar()->pluck('email')->all();
    }

    public function daftarArea(): array
    {
        return AreaRak::daftar();
    }

    public function simpan(array $data): AksesArea
    {
        return AksesArea::create($this->siapkan($data, $data['email']));
    }

    public function perbarui(AksesArea $akses, array $data): void
    {
        $akses->update($this->siapkan(
            collect($data)->except('email')->all(),
            $akses->email,
        ));
    }

    public function hapus(AksesArea $akses): void
    {
        $akses->delete();
    }

    private function queryUserTerdaftar()
    {
        return AdminUser::query()
            ->whereIn('email', MenuAkses::query()->distinct()->pluck('email')->all());
    }

    /**
     * `username` tidak lagi diketik admin, tetapi kolomnya tetap diisi karena
     * sistem lain di ekosistem H3 ikut membaca tabel ini. Nilainya diambil dari
     * nama pemilik akun di DMS.
     *
     * Level admin berarti semua area, jadi kolom `area` dikosongkan supaya
     * tidak ada dua nilai yang bisa bertentangan.
     */
    private function siapkan(array $data, string $email): array
    {
        $data['username'] = mb_substr(
            AdminUser::query()->where('email', $email)->value('name') ?? $email,
            0,
            self::PANJANG_USERNAME,
        );

        if ((int) ($data['level'] ?? 0) === AksesArea::LEVEL_ADMIN) {
            $data['area'] = null;
        }

        return $data;
    }
}
