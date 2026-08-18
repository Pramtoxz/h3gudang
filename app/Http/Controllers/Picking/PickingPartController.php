<?php

namespace App\Http\Controllers\Picking;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Services\Picking\AreaOperatorService;
use App\Services\Picking\PickingPartService;
use App\Services\Picking\SinkronisasiDoService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PickingPartController extends Controller
{
    public function __construct(
        private readonly PickingPartService $pickingPart,
        private readonly AreaOperatorService $areaOperator,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->user();
        $saring = $this->saringDari($request);

        return Inertia::render('picking/picking-part/Index', [
            'daftarDo' => $this->pickingPart->daftarDo($user, $saring),
            'daftarAreaChannel' => $this->pickingPart->daftarAreaChannel(),
            'areaOperator' => $this->areaOperator->areaUntuk($user),
            'saring' => $saring,
        ]);
    }

    /**
     * Tombol manual memicu command yang sama dengan cron, jadi tidak ada dua
     * jalur sinkronisasi yang bisa berbeda perilaku.
     */
    public function sync(SinkronisasiDoService $sinkronisasi): RedirectResponse
    {
        $hasil = $sinkronisasi->jalankan();

        return back()->with('success', sprintf(
            'Sinkronisasi selesai — %d baris dibaca, %d disimpan.',
            $hasil['dibaca'],
            $hasil['disimpan'],
        ));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $fkDo = $request->validate(['fk_do' => ['required', 'string']])['fk_do'];

        $jumlah = $this->pickingPart->hapusDo($fkDo);

        if ($jumlah === 0) {
            return back()->with('error', 'Tidak ada data untuk DO '.$fkDo.'.');
        }

        return back()->with('success', sprintf('%d item DO %s dihapus.', $jumlah, $fkDo));
    }

    private function user(): AdminUser
    {
        $user = Auth::user();

        abort_unless($user instanceof AdminUser, 403);

        return $user;
    }

    private function saringDari(Request $request): array
    {
        return [
            'area' => $request->query('area') ?: null,
            'status' => $request->query('status') ?: null,
            'tgl_dari' => $request->query('tgl_dari') ?: null,
            'tgl_sampai' => $request->query('tgl_sampai') ?: null,
            'cari' => $request->query('cari') ?: null,
        ];
    }
}
