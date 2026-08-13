<?php

namespace App\Services\Pmo;

use App\Repositories\SalesSupervisorRepository;
use App\Support\PenulisSheetExcel;
use Spatie\SimpleExcel\SimpleExcelWriter;

class SalesSupervisorExportService
{
    private const HEADER = ['NAMA', 'EMAIL', 'JABATAN', 'NOHP', 'KODE'];

    public function __construct(private readonly SalesSupervisorRepository $salesSupervisorRepository)
    {
    }

    public function unduh(): mixed
    {
        $writer = SimpleExcelWriter::streamDownload('data-sales-spv-'.now()->format('Y-m-d').'.xlsx');

        $writer->nameCurrentSheet('Sales & SPV');
        PenulisSheetExcel::tulis($writer, self::HEADER, $this->baris());

        return $writer->toBrowser();
    }

    private function baris(): array
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
