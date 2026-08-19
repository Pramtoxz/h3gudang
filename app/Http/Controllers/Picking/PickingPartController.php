<?php

namespace App\Http\Controllers\Picking;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Services\Picking\AreaOperatorService;
use App\Services\Picking\PickingPartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function detail(string $fkDo): Response
    {
        $user = $this->user();

        return Inertia::render('picking/picking-part/Detail', [
            'fkDo' => $fkDo,
            'daftarPart' => $this->pickingPart->daftarPartDalamDo($user, $fkDo),
            'isAdmin' => $user->it === 't',
        ]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'status' => ['required', 'in:done,waiting'],
        ]);

        $hasil = $this->pickingPart->updateStatusPart($validated['id'], $validated['status']);

        return response()->json([
            'success' => true,
            'message' => $hasil['message'],
            'waktu_done' => $hasil['waktu_done'],
        ]);
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
