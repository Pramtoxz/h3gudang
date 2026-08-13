<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\Menu;
use App\Models\MenuAkses;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as KoleksiDasar;
use Illuminate\Support\Str;

class NavigasiService
{
    private const IZIN_PER_AKHIRAN = [
        'index' => 'boleh_lihat',
        'show' => 'boleh_lihat',
        'create' => 'boleh_tambah',
        'store' => 'boleh_tambah',
        'edit' => 'boleh_ubah',
        'update' => 'boleh_ubah',
        'destroy' => 'boleh_hapus',
    ];

    /**
     * Project yang boleh dibuka user. Untuk user biasa daftarnya diturunkan
     * dari menu yang dia miliki, jadi tidak ada tabel akses project terpisah
     * yang harus dijaga tetap sinkron.
     */
    public function projectUntuk(AdminUser $user): array
    {
        $query = Project::query()->where('aktif', true)->orderBy('urutan');

        if (! $user->isIt()) {
            $query->whereIn('id', $this->projectIdYangDimiliki($user));
        }

        return $query->get(['id', 'kode', 'nama', 'keterangan', 'ikon'])
            ->map(fn (Project $project): array => [
                'id' => $project->id,
                'kode' => $project->kode,
                'nama' => $project->nama,
                'keterangan' => $project->keterangan,
                'ikon' => $project->ikon,
                'url_awal' => $this->urlAwal($user, $project->id),
            ])
            ->all();
    }

    /**
     * Gerbang login web: pengelola IT selalu boleh, selain itu harus punya
     * minimal satu menu yang boleh dilihat.
     */
    public function punyaAkses(AdminUser $user): bool
    {
        return $user->isIt()
            || MenuAkses::query()
                ->where('email', $user->email)
                ->where('boleh_lihat', true)
                ->exists();
    }

    /**
     * Izin per aksi untuk modul yang sedang dibuka. Dipakai ShareMenus supaya
     * React bisa menyembunyikan tombol yang tidak boleh dipakai.
     *
     * Ini kenyamanan tampilan, BUKAN pengamanan. Yang menahan permintaan tetap
     * middleware CheckMenuAccess.
     */
    public function izinUntuk(AdminUser $user, ?string $namaRoute): array
    {
        if ($user->isIt()) {
            return $this->bentukIzin(true, true, true, true);
        }

        if (! $namaRoute) {
            return $this->bentukIzin(false, false, false, false);
        }

        $akses = MenuAkses::query()
            ->where('email', $user->email)
            ->whereHas('menu', fn ($query) => $query
                ->aktif()
                ->where('route', 'like', $this->prefixModul($namaRoute).'%'))
            ->first();

        return $this->bentukIzin(
            (bool) $akses?->boleh_lihat,
            (bool) $akses?->boleh_tambah,
            (bool) $akses?->boleh_ubah,
            (bool) $akses?->boleh_hapus,
        );
    }

    /**
     * Kolom izin yang dibutuhkan sebuah route. Ditentukan dari akhiran nama
     * route; kalau akhirannya di luar CRUD baku (export, import, reset-pin,
     * sync), penentunya metode HTTP.
     */
    public function kolomIzin(string $namaRoute, string $metodeHttp): string
    {
        $akhiran = Str::afterLast($namaRoute, '.');

        if (isset(self::IZIN_PER_AKHIRAN[$akhiran])) {
            return self::IZIN_PER_AKHIRAN[$akhiran];
        }

        return in_array(strtoupper($metodeHttp), ['GET', 'HEAD', 'OPTIONS'], true)
            ? 'boleh_lihat'
            : 'boleh_ubah';
    }

    private function bentukIzin(bool $lihat, bool $tambah, bool $ubah, bool $hapus): array
    {
        return [
            'lihat' => $lihat,
            'tambah' => $tambah,
            'ubah' => $ubah,
            'hapus' => $hapus,
        ];
    }

    public function menuUntuk(AdminUser $user, ?int $projectId): array
    {
        $menu = $this->menuYangTerlihat($user, $projectId);

        return $this->susunPohon($menu);
    }

    /**
     * Halaman pendaratan setelah login: menu pertama yang benar-benar dimiliki
     * user, supaya orang yang tidak punya Dashboard tidak mendarat di 403.
     */
    public function urlAwal(AdminUser $user, ?int $projectId): ?string
    {
        foreach ($this->menuUntuk($user, $projectId) as $induk) {
            if ($induk['url']) {
                return $induk['url'];
            }

            foreach ($induk['children'] as $anak) {
                if ($anak['url']) {
                    return $anak['url'];
                }
            }
        }

        return null;
    }

    /**
     * Dipakai middleware untuk memutuskan apakah sebuah route boleh dibuka.
     * Nama route admin berbentuk `<project>.<modul>.<aksi>`: dua segmen pertama
     * menentukan modulnya, segmen terakhir menentukan aksi yang dituntut.
     */
    public function bolehMembuka(AdminUser $user, string $namaRoute, string $metodeHttp = 'GET'): bool
    {
        if ($user->isIt()) {
            return true;
        }

        $prefix = $this->prefixModul($namaRoute);

        return MenuAkses::query()
            ->where('email', $user->email)
            ->where($this->kolomIzin($namaRoute, $metodeHttp), true)
            ->whereHas('menu', fn ($query) => $query
                ->aktif()
                ->where('khusus_it', false)
                ->where('route', 'like', $prefix.'%'))
            ->exists();
    }

    public function prefixModul(string $namaRoute): string
    {
        $segmen = explode('.', $namaRoute);

        return count($segmen) > 2
            ? $segmen[0].'.'.$segmen[1]
            : $namaRoute;
    }

    private function menuYangTerlihat(AdminUser $user, ?int $projectId): Collection
    {
        $query = Menu::query()
            ->aktif()
            ->untukProject($projectId)
            ->orderBy('urutan');

        if (! $user->isIt()) {
            $idDimiliki = $this->menuIdYangDimiliki($user);
            $idInduk = Menu::query()->whereIn('id', $idDimiliki)->pluck('parent_id')->filter()->all();

            $query->where('khusus_it', false)
                ->where(fn ($q) => $q->whereIn('id', $idDimiliki)->orWhereIn('id', $idInduk));
        }

        return $query->get();
    }

    private function susunPohon(Collection $menu): array
    {
        $anak = $menu->whereNotNull('parent_id')->groupBy('parent_id');

        return $menu->whereNull('parent_id')
            ->map(fn (Menu $induk): array => [
                ...$this->atribut($induk),
                'children' => $anak->get($induk->id, collect())
                    ->map(fn (Menu $item): array => $this->atribut($item))
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    private function atribut(Menu $menu): array
    {
        return [
            'id' => $menu->id,
            'nama_menu' => $menu->nama_menu,
            'ikon' => $menu->ikon,
            'route' => $menu->route,
            'url' => $menu->url,
        ];
    }

    private function menuIdYangDimiliki(AdminUser $user): array
    {
        return MenuAkses::query()
            ->where('email', $user->email)
            ->where('boleh_lihat', true)
            ->pluck('menu_id')
            ->all();
    }

    private function projectIdYangDimiliki(AdminUser $user): KoleksiDasar
    {
        return Menu::query()
            ->whereIn('id', $this->menuIdYangDimiliki($user))
            ->whereNotNull('project_id')
            ->distinct()
            ->pluck('project_id');
    }
}
