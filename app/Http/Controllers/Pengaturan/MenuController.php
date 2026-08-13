<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengaturan\SimpanMenuRequest;
use App\Models\Menu;
use App\Services\Pengaturan\MenuService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function __construct(private readonly MenuService $menuService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('pengaturan/menu/Index', [
            'daftarProject' => $this->menuService->daftarProject(),
            'daftarMenu' => $this->menuService->pohonMenu(),
        ]);
    }

    public function store(SimpanMenuRequest $request): RedirectResponse
    {
        $this->menuService->simpan($request->validated());

        return to_route('pengaturan.menu.index')->with('success', 'Menu berhasil ditambahkan');
    }

    public function update(SimpanMenuRequest $request, Menu $menu): RedirectResponse
    {
        $this->menuService->perbarui($menu, $request->validated());

        return to_route('pengaturan.menu.index')->with('success', 'Menu berhasil diperbarui');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->menuService->hapus($menu);

        return to_route('pengaturan.menu.index')->with('success', 'Menu berhasil dihapus');
    }
}
