<?php

namespace App\Http\Controllers\Picking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Picking\HapusMassalLokasiRakRequest;
use App\Http\Requests\Picking\SimpanLokasiRakRequest;
use App\Models\H3\LokasiRak;
use App\Services\Picking\LokasiRakService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LokasiRakController extends Controller
{
    public function __construct(private readonly LokasiRakService $lokasiRakService)
    {
    }

    public function index(Request $request): Response
    {
        $area = $request->query('area');
        $kodeGudang = array_values(array_filter(explode(',', (string) $request->query('gudang'))));

        return Inertia::render('picking/lokasi-rak/Index', [
            'ringkasanArea' => $this->lokasiRakService->ringkasanArea(),
            'daftarGudang' => $this->lokasiRakService->daftarGudang(),
            'daftarJenisLokasi' => $this->lokasiRakService->daftarJenisLokasi(),
            'detailLokasi' => Inertia::optional(
                fn (): array => $area === null || $kodeGudang === []
                    ? []
                    : $this->lokasiRakService->detailLokasi($area, $kodeGudang),
            ),
        ]);
    }

    public function store(SimpanLokasiRakRequest $request): RedirectResponse
    {
        $this->lokasiRakService->simpan($request->validated());

        return back()->with('success', 'Lokasi rak berhasil ditambahkan');
    }

    public function update(SimpanLokasiRakRequest $request, LokasiRak $lokasiRak): RedirectResponse
    {
        $this->lokasiRakService->perbarui($lokasiRak, $request->validated());

        return back()->with('success', 'Lokasi rak berhasil diperbarui');
    }

    public function destroy(LokasiRak $lokasiRak): RedirectResponse
    {
        $this->lokasiRakService->hapus($lokasiRak);

        return back()->with('success', 'Lokasi rak '.$lokasiRak->kd_lokasi.' berhasil dihapus');
    }

    public function destroyMassal(HapusMassalLokasiRakRequest $request): RedirectResponse
    {
        $jumlah = $this->lokasiRakService->hapusMassal(
            $request->validated('area_rak'),
            $request->validated('kode_gudang'),
        );

        return back()->with(
            'success',
            $jumlah.' lokasi rak pada area '.$request->validated('area_rak').' berhasil dihapus',
        );
    }
}
