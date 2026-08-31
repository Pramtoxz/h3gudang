<?php

namespace App\Http\Controllers\Picking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Picking\SimpanFinalCheckRequest;
use App\Models\AdminUser;
use App\Services\Picking\FinalCheckService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FinalCheckController extends Controller
{
    public function __construct(private readonly FinalCheckService $finalCheck)
    {
    }

    public function index(Request $request): Response
    {
        $saring = $this->saringDari($request);

        return Inertia::render('picking/final-check/Index', [
            'daftarDo' => $this->finalCheck->daftarDo($saring),
            'daftarAreaChannel' => $this->finalCheck->daftarAreaChannel(),
            'saring' => $saring,
        ]);
    }

    /**
     * Nomor DO berisi garis miring, jadi tidak bisa jadi segment path — sama
     * seperti Picking Part, dipakai query parameter `?do=...`.
     */
    public function detail(Request $request): Response
    {
        $fkDo = $request->query('do');

        abort_unless(is_string($fkDo) && $fkDo !== '', 404, 'Parameter DO tidak ditemukan.');

        $info = $this->finalCheck->infoDo($fkDo);

        abort_unless($info !== null, 404, 'DO tidak ditemukan atau belum ada part yang selesai diambil.');

        return Inertia::render('picking/final-check/Detail', [
            'fkDo' => $fkDo,
            'infoDo' => $info,
            'daftarPart' => $this->finalCheck->daftarPartDalamDo($fkDo),
            'isBundling' => $this->finalCheck->doBundling($fkDo),
        ]);
    }

    public function simpan(SimpanFinalCheckRequest $request): RedirectResponse
    {
        $fkDo = $request->validated('fk_do');

        $hasil = $this->finalCheck->simpanDanFinalkan($this->user(), $fkDo, $request->dataFinal());

        return back()->with('success', sprintf(
            'Final check DO %s tersimpan: %d nomor kotak dicatat, %d part difinalkan.',
            $fkDo,
            $hasil['jumlah_kotak'],
            $hasil['jumlah_final'],
        ));
    }

    private function user(): AdminUser
    {
        $user = Auth::user();

        abort_unless($user instanceof AdminUser, 403);

        return $user;
    }

    /**
     * @return array<string, ?string>
     */
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
