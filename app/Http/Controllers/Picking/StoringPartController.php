<?php

namespace App\Http\Controllers\Picking;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\H3\GudangPart;
use App\Services\Picking\AreaOperatorService;
use App\Services\Picking\StoringPartService;
use App\Support\Picking\AreaRak;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StoringPartController extends Controller
{
    public function __construct(
        private readonly StoringPartService $storingPart,
        private readonly AreaOperatorService $areaOperator,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->user();
        $saring = $this->saringDari($request);

        return Inertia::render('picking/storing-part/Index', [
            'daftarStoring' => $this->storingPart->daftarStoring($user, $saring),
            'daftarAreaRak' => AreaRak::daftar(),
            'daftarGudang' => GudangPart::query()->where('gudang_part_active', true)->get(['kd_gudang_part', 'nm_gudang_part']),
            'areaOperator' => $this->areaOperator->areaUntuk($user),
            'isAdmin' => $this->areaOperator->adalahAdminArea($user),
            'saring' => $saring,
        ]);
    }

    public function detail(Request $request): Response
    {
        $fkDo = $request->query('do');

        abort_unless(is_string($fkDo) && $fkDo !== '', 404, 'Parameter dokumen penerimaan tidak ditemukan.');

        $user = $this->user();
        $detail = $this->storingPart->detailStoring($user, $fkDo);

        return Inertia::render('picking/storing-part/Detail', [
            'fkDo' => $fkDo,
            'dokumen' => $detail['dokumen'],
            'daftarPart' => $detail['parts'],
            'areaOperator' => $this->areaOperator->areaUntuk($user),
            'isAdmin' => $this->areaOperator->adalahAdminArea($user),
            'urlKembali' => $this->urlKembali($request),
        ]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'status' => ['required', 'in:waiting'],
        ]);

        $hasil = $this->storingPart->updateStatusPart($this->user(), $validated['id'], $validated['status']);

        return response()->json($hasil);
    }


    public function sync(Request $request): JsonResponse
    {
        $hasil = $this->storingPart->syncPenerimaan();

        return response()->json([
            'success' => true,
            'message' => sprintf(
                'Sinkronisasi selesai. Dibaca: %d, Baru: %d, Diperbarui: %d, Dilewati: %d.',
                $hasil['dibaca'],
                $hasil['disimpan'],
                $hasil['diperbarui'],
                $hasil['dilewati']
            ),
            'data' => $hasil,
        ]);
    }

    private function user(): AdminUser
    {
        /** @var AdminUser $user */
        $user = Auth::guard('web')->user();

        return $user;
    }

    /**
     * @return array{area: ?string, status: string, tgl_dari: ?string, tgl_sampai: ?string, cari: ?string, gudang: ?string}
     */
    private function saringDari(Request $request): array
    {
        return [
            'area' => $request->query('area') ?: null,
            'gudang' => $request->query('gudang') ?: null,
            'status' => $request->query('status', StoringPartService::STATUS_BAWAAN),
            'tgl_dari' => $request->query('tgl_dari') ?: null,
            'tgl_sampai' => $request->query('tgl_sampai') ?: null,
            'cari' => $request->query('cari') ?: null,
        ];
    }

    private function urlKembali(Request $request): string
    {
        $dari = $request->query('dari');

        if (is_string($dari) && str_starts_with($dari, '/picking/storing-part')) {
            return $dari;
        }

        return route('picking.storing-part.index');
    }
}
