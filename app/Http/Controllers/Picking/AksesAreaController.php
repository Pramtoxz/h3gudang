<?php

namespace App\Http\Controllers\Picking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Picking\SimpanAksesAreaRequest;
use App\Models\H3\AksesArea;
use App\Services\Picking\AksesAreaService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AksesAreaController extends Controller
{
    public function __construct(private readonly AksesAreaService $aksesAreaService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('picking/akses-area/Index', [
            'daftarAkses' => $this->aksesAreaService->daftar(),
            'daftarUser' => $this->aksesAreaService->daftarUserTerdaftar(),
            'daftarArea' => $this->aksesAreaService->daftarArea(),
        ]);
    }

    public function store(SimpanAksesAreaRequest $request): RedirectResponse
    {
        $this->aksesAreaService->simpan($request->validated());

        return back()->with('success', 'Akses area berhasil ditambahkan');
    }

    public function update(SimpanAksesAreaRequest $request, AksesArea $aksesArea): RedirectResponse
    {
        $this->aksesAreaService->perbarui($aksesArea, $request->validated());

        return back()->with('success', 'Akses area berhasil diperbarui');
    }

    public function destroy(AksesArea $aksesArea): RedirectResponse
    {
        $this->aksesAreaService->hapus($aksesArea);

        return back()->with('success', 'Akses area '.$aksesArea->email.' berhasil dihapus');
    }
}
