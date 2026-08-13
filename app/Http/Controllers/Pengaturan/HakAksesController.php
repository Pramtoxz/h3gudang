<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengaturan\SimpanHakAksesRequest;
use App\Services\Pengaturan\HakAksesService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HakAksesController extends Controller
{
    public function __construct(private readonly HakAksesService $hakAksesService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('pengaturan/hak-akses/Index', [
            'daftarUser' => $this->hakAksesService->daftarUser(),
            'daftarMenu' => $this->hakAksesService->menuYangBisaDiberikan(),
            'petaAkses' => $this->hakAksesService->petaAkses(),
        ]);
    }

    public function update(SimpanHakAksesRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->hakAksesService->simpan($data['email'], $data['izin']);

        return to_route('pengaturan.hak-akses.index')
            ->with('success', 'Hak akses untuk '.$data['email'].' berhasil disimpan');
    }
}
