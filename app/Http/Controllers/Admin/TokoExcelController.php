<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportExcelRequest;
use App\Services\Admin\TokoExportService;
use App\Services\Admin\TokoImportService;
use App\Support\RingkasanImport;
use Illuminate\Http\RedirectResponse;
use Throwable;

class TokoExcelController extends Controller
{
    public function __construct(
        private readonly TokoExportService $tokoExportService,
        private readonly TokoImportService $tokoImportService,
    ) {
    }

    public function export(): mixed
    {
        return $this->tokoExportService->unduh();
    }

    public function import(ImportExcelRequest $request): RedirectResponse
    {
        try {
            $hasil = $this->tokoImportService->proses($request->lokasiBerkas(), $request->ekstensi());
        } catch (Throwable $e) {
            return to_route('admin.toko.index')
                ->with('error', 'Gagal import data: '.$e->getMessage());
        }

        return to_route('admin.toko.index')
            ->with('success', RingkasanImport::pesan('Data toko', $hasil));
    }
}
