<?php

namespace App\Services\Pengaturan;

use App\Models\AdminUser;
use App\Models\Menu;
use App\Models\MenuAkses;
use Illuminate\Support\Facades\DB;

class HakAksesService
{
    /**
     * Daftar calon penerima akses diambil dari DMS (read-only). Kolom `it`
     * ikut dikirim supaya pengelola terlihat jelas dan tidak perlu dicentang.
     */
    public function daftarUser(): array
    {
        return AdminUser::query()
            ->orderBy('name')
            ->get(['name', 'email', 'it'])
            ->map(fn (AdminUser $user): array => [
                'nama' => $user->name,
                'email' => $user->email,
                'is_it' => $user->isIt(),
            ])
            ->all();
    }

    public function menuYangBisaDiberikan(): array
    {
        return Menu::query()
            ->with('project:id,kode,nama')
            ->where('khusus_it', false)
            ->whereNotNull('project_id')
            ->orderBy('project_id')
            ->orderBy('urutan')
            ->get()
            ->map(fn (Menu $menu): array => [
                'id' => $menu->id,
                'nama_menu' => $menu->nama_menu,
                'project_kode' => $menu->project?->kode,
                'project_nama' => $menu->project?->nama,
            ])
            ->all();
    }

    /**
     * Peta email -> menu_id -> izin tiap aksi, hanya untuk user yang sudah
     * punya akses.
     */
    public function petaAkses(): array
    {
        return MenuAkses::query()
            ->get(['email', 'menu_id', 'boleh_lihat', 'boleh_tambah', 'boleh_ubah', 'boleh_hapus'])
            ->groupBy('email')
            ->map(fn ($baris) => $baris
                ->mapWithKeys(fn (MenuAkses $akses): array => [
                    $akses->menu_id => [
                        'lihat' => $akses->boleh_lihat,
                        'tambah' => $akses->boleh_tambah,
                        'ubah' => $akses->boleh_ubah,
                        'hapus' => $akses->boleh_hapus,
                    ],
                ])
                ->all())
            ->all();
    }

    /**
     * Baris tanpa izin lihat tidak disimpan sama sekali — menu yang tidak boleh
     * dilihat sama artinya dengan tidak diberikan.
     */
    public function simpan(string $email, array $daftarIzin): void
    {
        DB::connection('pgsql')->transaction(function () use ($email, $daftarIzin): void {
            MenuAkses::query()->where('email', $email)->delete();

            $baris = collect($daftarIzin)
                ->filter(fn (array $izin): bool => (bool) ($izin['lihat'] ?? false))
                ->unique('menu_id')
                ->map(fn (array $izin): array => [
                    'email' => $email,
                    'menu_id' => (int) $izin['menu_id'],
                    'boleh_lihat' => true,
                    'boleh_tambah' => (bool) ($izin['tambah'] ?? false),
                    'boleh_ubah' => (bool) ($izin['ubah'] ?? false),
                    'boleh_hapus' => (bool) ($izin['hapus'] ?? false),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->values()
                ->all();

            if ($baris === []) {
                return;
            }

            MenuAkses::query()->insert($baris);
        });
    }
}
