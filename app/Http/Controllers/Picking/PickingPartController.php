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

    /**
     * Nomor DO berisi slash (mis. `2025/019327/DO-OTHER`), jadi tidak bisa
     * jadi path segment — Laravel akan memecahnya dan memberi 404. Aplikasi
     * lama memakai query parameter `?do=...`; perilaku itu dipertahankan.
     */
    public function detail(Request $request): Response
    {
        $fkDo = $request->query('do');

        abort_unless(is_string($fkDo) && $fkDo !== '', 404, 'Parameter DO tidak ditemukan.');

        $user = $this->user();

        return Inertia::render('picking/picking-part/Detail', [
            'fkDo' => $fkDo,
            'daftarPart' => $this->pickingPart->daftarPartDalamDo($user, $fkDo),
            'isBundling' => $this->pickingPart->doBundling($fkDo),
            'isAdmin' => $this->bolehKelola($user),
        ]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'status' => ['required', 'in:done,waiting'],
        ]);

        $hasil = $this->pickingPart->updateStatusPart($validated['id'], $validated['status']);

        return response()->json($hasil);
    }

    /**
     * Menyimpan input Kartu Stok keluar setelah operator menandai part Done.
     * Dipanggil modal yang mengunci halaman — qty harus sama persis dengan
     * `qty_part` (validasi di service, seluruh batch ditolak bila melenceng).
     */
    public function kartustok(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.fk_do' => ['required', 'string'],
            'items.*.fk_dealer' => ['required', 'string'],
            'items.*.fk_part' => ['required', 'string'],
            'items.*.lokasi_part' => ['required', 'string'],
            'items.*.jumlah_input' => ['required', 'integer', 'min:1'],
        ]);

        $jumlah = $this->pickingPart->simpanKartuStokKeluar($validated['items']);

        return response()->json([
            'success' => true,
            'message' => 'Kartu stok tersimpan.',
            'inserted' => $jumlah,
        ]);
    }

    /**
     * Hapus satu baris part dari DO — apa pun statusnya. Meniru
     * `deleteDOItemByAdmin()`: hanya admin (level 1), respons JSON.
     */
    public function hapusItem(Request $request): JsonResponse
    {
        $user = $this->user();

        abort_unless($this->bolehKelola($user), 403, 'Akses ditolak. Hanya admin yang dapat melakukan aksi ini.');

        $id = $request->validate(['id' => ['required', 'integer']])['id'];

        $jumlah = $this->pickingPart->hapusItem($id);

        if ($jumlah === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Data item tidak ditemukan (ID: '.$id.').',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menghapus item dari DO.',
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

    /**
     * Meniru pemeriksaan `M_Level::level == '1'` milik aplikasi lama: di @new
     * admin = user DMS dengan flag IT (`kolom it = 't'`).
     */
    private function bolehKelola(AdminUser $user): bool
    {
        return $user->it === 't';
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
