<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Project;
use Illuminate\Database\Seeder;

/**
 * Menu dicocokkan lewat kolom `route` yang unik, bukan id, supaya seeder ini
 * tetap benar meski dijalankan ulang di atas data yang sudah ada.
 */
class MenuSeeder extends Seeder
{
    private const MENU_PMO = [
        ['Dashboard', 'LayoutDashboard', 'pmo.dashboard', '/pmo/dashboard', 1],
        ['Kelola Toko', 'Store', 'pmo.toko.index', '/pmo/toko', 2],
        ['Sales Supervisor', 'UsersRound', 'pmo.sales-spv.index', '/pmo/sales-spv', 3],
        ['Campaign', 'Megaphone', 'pmo.campaigns.index', '/pmo/campaigns', 4],
        ['Katalog Motor', 'BookOpen', 'pmo.katalog.index', '/pmo/katalog', 5],
        ['Gambar Kategori Part', 'Images', 'pmo.category-images.index', '/pmo/category-images', 6],
        ['Part Populer', 'Star', 'pmo.popular-parts.index', '/pmo/popular-parts', 7],
        ['Notifikasi', 'Bell', 'pmo.notifications.index', '/pmo/notifications', 8],
    ];

    private const MENU_PICKING = [
        ['Picking Part', 'PackageSearch', 'picking.picking-part.index', '/picking/picking-part', 1],
        ['Master Channel', 'Building2', 'picking.channel.index', '/picking/channel', 10],
        ['Master Lokasi Rak', 'MapPin', 'picking.lokasi-rak.index', '/picking/lokasi-rak', 11],
        ['Master Akses Area', 'UserCog', 'picking.akses-area.index', '/picking/akses-area', 12],
    ];

    private const MENU_PENGATURAN = [
        ['Kelola Menu', 'List', 'pengaturan.menu.index', '/pengaturan/menu', 1],
        ['Kelola Hak Akses', 'ShieldCheck', 'pengaturan.hak-akses.index', '/pengaturan/hak-akses', 2],
    ];

    public function run(): void
    {
        $this->menuProject('pmo', self::MENU_PMO);
        $this->menuProject('picking', self::MENU_PICKING);
        $this->menuPengaturan();
    }

    private function menuProject(string $kodeProject, array $daftar): void
    {
        $project = Project::query()->where('kode', $kodeProject)->first();

        if (! $project) {
            return;
        }

        foreach ($daftar as [$nama, $ikon, $route, $url, $urutan]) {
            Menu::query()->updateOrCreate(
                ['route' => $route],
                [
                    'project_id' => $project->id,
                    'nama_menu' => $nama,
                    'ikon' => $ikon,
                    'url' => $url,
                    'parent_id' => null,
                    'urutan' => $urutan,
                    'status_aktif' => true,
                    'khusus_it' => false,
                ],
            );
        }
    }

    /**
     * Grup Pengaturan memakai project_id NULL supaya tampil di project mana pun,
     * dan khusus_it supaya tidak pernah bisa dicentangkan ke user biasa.
     */
    private function menuPengaturan(): void
    {
        $induk = Menu::query()->firstOrCreate(
            ['nama_menu' => 'Pengaturan', 'parent_id' => null],
            [
                'project_id' => null,
                'ikon' => 'Settings',
                'route' => null,
                'url' => null,
                'urutan' => 99,
                'status_aktif' => true,
                'khusus_it' => true,
            ],
        );

        foreach (self::MENU_PENGATURAN as [$nama, $ikon, $route, $url, $urutan]) {
            Menu::query()->updateOrCreate(
                ['route' => $route],
                [
                    'project_id' => null,
                    'nama_menu' => $nama,
                    'ikon' => $ikon,
                    'url' => $url,
                    'parent_id' => $induk->id,
                    'urutan' => $urutan,
                    'status_aktif' => true,
                    'khusus_it' => true,
                ],
            );
        }
    }
}
