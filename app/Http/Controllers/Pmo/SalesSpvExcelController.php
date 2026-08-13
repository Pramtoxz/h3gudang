<?php

namespace App\Http\Controllers\Pmo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pmo\ImportExcelRequest;
use App\Services\Pmo\SalesSupervisorExportService;
use App\Services\Pmo\SalesSupervisorImportService;
use App\Support\RingkasanImport;
use Illuminate\Http\RedirectResponse;
use Throwable;

class SalesSpvExcelController extends Controller
{
    public function __construct(
        private readonly SalesSupervisorExportService $exportService,
        private readonly SalesSupervisorImportService $importService,
    ) {
    }

    public function export(): mixed
    {
        return $this->exportService->unduh();
    }

    public function import(ImportExcelRequest $request): RedirectResponse
    {
        try {
            $hasil = $this->importService->proses($request->lokasiBerkas(), $request->ekstensi());
        } catch (Throwable $e) {
            return to_route('pmo.sales-spv.index')
                ->with('error', 'Gagal import data: '.$e->getMessage());
        }

        return to_route('pmo.sales-spv.index')
            ->with('success', RingkasanImport::pesan('Data sales & supervisor', $hasil));
    }
}
