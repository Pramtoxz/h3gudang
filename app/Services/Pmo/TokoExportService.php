<?php

namespace App\Services\Pmo;

use App\Repositories\SalesSupervisorRepository;
use App\Repositories\TokoRepository;
use App\Support\PenulisSheetExcel;
use Spatie\SimpleExcel\SimpleExcelWriter;

class TokoExportService
{
    private const HEADER_TOKO = ['NAMA TOKO', 'SALESMAN', 'SPV', 'KODE', 'EMAIL', 'NOHP', 'STATUS'];

    private const HEADER_SALES_SPV = ['NAMA', 'EMAIL', 'JABATAN', 'NOHP', 'KODE'];

    public function __construct(
        private readonly TokoRepository $tokoRepository,
        private readonly SalesSupervisorRepository $salesSupervisorRepository,
    ) {
    }

    public function unduh(): mixed
    {
        $writer = SimpleExcelWriter::streamDownload('data-toko-'.now()->format('Y-m-d').'.xlsx');

        $writer->nameCurrentSheet('Toko');
        PenulisSheetExcel::tulis($writer, self::HEADER_TOKO, $this->barisToko());

        $writer->addNewSheetAndMakeItCurrent('Sales & SPV');
        PenulisSheetExcel::tulis($writer, self::HEADER_SALES_SPV, $this->barisSalesSpv());

        return $writer->toBrowser();
    }

    private function barisToko(): array
    {
        return array_map(fn (object $baris): array => [
            'NAMA TOKO' => $baris->nama_toko,
            'SALESMAN' => $baris->salesman,
            'SPV' => $baris->spv,
            'KODE' => $baris->kode,
            'EMAIL' => $baris->email,
            'NOHP' => $baris->nohp,
            'STATUS' => $baris->status,
        ], $this->tokoRepository->barisExportToko());
    }

    private function barisSalesSpv(): array
    {
        return array_map(fn (object $baris): array => [
            'NAMA' => $baris->nama,
            'EMAIL' => $baris->email,
            'JABATAN' => $baris->jabatan,
            'NOHP' => $baris->nohp,
            'KODE' => $baris->kode,
        ], $this->salesSupervisorRepository->barisExport());
    }

}
