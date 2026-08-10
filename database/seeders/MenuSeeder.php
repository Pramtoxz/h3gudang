<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\MenuRole;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        MenuRole::query()->delete();
        Menu::query()->delete();

        $menus = [
            [
                'nama_menu' => 'Dashboard',
                'ikon' => 'LayoutDashboard',
                'route' => 'dashboard',
                'url' => '/dashboard',
                'parent_id' => null,
                'urutan' => 1,
                'status_aktif' => true,
                'roles' => ['Admin'],
            ],
            [
                'nama_menu' => 'Pengaturan',
                'ikon' => 'Settings',
                'route' => null,
                'url' => null,
                'parent_id' => null,
                'urutan' => 2,
                'status_aktif' => true,
                'roles' => ['Admin'],
                'children' => [
                    [
                        'nama_menu' => 'Menu Management',
                        'ikon' => 'ListTree',
                        'route' => 'settings.menus.index',
                        'url' => '/settings/menus',
                        'urutan' => 1,
                        'status_aktif' => true,
                        'roles' => ['Admin'],
                    ],
                ],
            ],
        ];

        foreach ($menus as $menuData) {
            $children = $menuData['children'] ?? [];
            $roles = $menuData['roles'];
            unset($menuData['children'], $menuData['roles']);

            $parent = Menu::create($menuData);

            foreach ($roles as $role) {
                MenuRole::create(['menu_id' => $parent->id, 'role' => $role]);
            }

            foreach ($children as $childData) {
                $childRoles = $childData['roles'];
                unset($childData['roles']);
                $childData['parent_id'] = $parent->id;

                $child = Menu::create($childData);

                foreach ($childRoles as $role) {
                    MenuRole::create(['menu_id' => $child->id, 'role' => $role]);
                }
            }
        }
    }
}
