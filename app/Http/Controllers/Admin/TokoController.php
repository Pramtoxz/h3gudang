<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\TokoMasihDipakaiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTokoRequest;
use App\Http\Requests\Admin\UpdateTokoRequest;
use App\Models\Toko;
use App\Services\Admin\TokoService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TokoController extends Controller
{
    public function __construct(private readonly TokoService $tokoService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('admin/toko/Index', [
            'daftarToko' => $this->tokoService->daftar(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/toko/Create');
    }

    public function store(StoreTokoRequest $request): RedirectResponse
    {
        $this->tokoService->simpan($request->validated());

        return to_route('admin.toko.index')->with('success', 'Toko berhasil ditambahkan');
    }

    public function show(Toko $toko): Response
    {
        return Inertia::render('admin/toko/Show', $this->tokoService->detail($toko));
    }

    public function edit(Toko $toko): Response
    {
        return Inertia::render('admin/toko/Edit', [
            'toko' => $this->tokoService->atribut($toko),
        ]);
    }

    public function update(UpdateTokoRequest $request, Toko $toko): RedirectResponse
    {
        $this->tokoService->perbarui($toko, $request->validated());

        return to_route('admin.toko.index')->with('success', 'Toko berhasil diperbarui');
    }

    public function destroy(Toko $toko): RedirectResponse
    {
        try {
            $this->tokoService->hapus($toko);
        } catch (TokoMasihDipakaiException $e) {
            return to_route('admin.toko.index')->with('error', $e->getMessage());
        }

        return to_route('admin.toko.index')->with('success', 'Toko berhasil dihapus');
    }
}
