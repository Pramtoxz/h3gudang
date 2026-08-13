<?php

namespace App\Services\Pengaturan;

use App\Models\Menu;
use App\Models\Project;

class MenuService
{
    public function daftarProject(): array
    {
        return Project::query()
            ->orderBy('urutan')
            ->get(['id', 'kode', 'nama', 'aktif'])
            ->map(fn (Project $project): array => [
                'id' => $project->id,
                'kode' => $project->kode,
                'nama' => $project->nama,
                'aktif' => $project->aktif,
            ])
            ->all();
    }

    public function pohonMenu(): array
    {
        return Menu::query()
            ->with(['children' => fn ($q) => $q->orderBy('urutan')])
            ->whereNull('parent_id')
            ->orderBy('urutan')
            ->get()
            ->map(fn (Menu $induk): array => [
                ...$this->atribut($induk),
                'children' => $induk->children->map(fn (Menu $anak): array => $this->atribut($anak))->all(),
            ])
            ->all();
    }

    public function simpan(array $data): Menu
    {
        return Menu::create($this->bersihkan($data));
    }

    public function perbarui(Menu $menu, array $data): void
    {
        $menu->update($this->bersihkan($data));
    }

    public function hapus(Menu $menu): void
    {
        $menu->delete();
    }

    /**
     * Menu anak selalu mewarisi project induknya supaya tidak ada sub-menu
     * yang nyasar ke project lain.
     */
    private function bersihkan(array $data): array
    {
        if (! empty($data['parent_id'])) {
            $induk = Menu::find($data['parent_id']);
            $data['project_id'] = $induk?->project_id;
        }

        return $data;
    }

    private function atribut(Menu $menu): array
    {
        return [
            'id' => $menu->id,
            'project_id' => $menu->project_id,
            'nama_menu' => $menu->nama_menu,
            'ikon' => $menu->ikon,
            'route' => $menu->route,
            'url' => $menu->url,
            'parent_id' => $menu->parent_id,
            'urutan' => $menu->urutan,
            'status_aktif' => $menu->status_aktif,
            'khusus_it' => $menu->khusus_it,
        ];
    }
}
