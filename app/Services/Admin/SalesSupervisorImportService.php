<?php

namespace App\Services\Admin;

use App\Repositories\SalesSupervisorRepository;
use Illuminate\Support\LazyCollection;
use Spatie\SimpleExcel\SimpleExcelReader;
use Throwable;

class SalesSupervisorImportService
{
    private const PEMISAH_CSV = ';';

    private const JABATAN_BAWAAN = 'salesman';

    /**
     * Berkas lama memakai judul kolom yang berbeda-beda untuk jabatan.
     */
    private const KUNCI_JABATAN = ['jabatan', 'spv_/_salesman', 'spv_salesman', 'spv'];

    public function __construct(private readonly SalesSupervisorRepository $salesSupervisorRepository)
    {
    }

    public function proses(string $path, string $ekstensi): array
    {
        $diproses = 0;
        $galat = [];

        foreach ($this->bacaBaris($path, $ekstensi) as $baris) {
            $kodeNpk = trim((string) ($baris['kode'] ?? ''));

            if ($kodeNpk === '') {
                continue;
            }

            try {
                $this->salesSupervisorRepository->simpanDariImport($kodeNpk, [
                    'nama' => $this->nama($baris, $kodeNpk),
                    'no_hp' => $this->nilaiAtauNull($baris['nohp'] ?? null),
                    'jabatan' => $this->jabatan($baris),
                    'aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $diproses++;
            } catch (Throwable $e) {
                $galat[] = "Baris kode {$kodeNpk}: {$e->getMessage()}";
            }
        }

        return ['diproses' => $diproses, 'galat' => $galat];
    }

    private function bacaBaris(string $path, string $ekstensi): LazyCollection
    {
        $tipe = $ekstensi === 'xlsx' ? 'xlsx' : 'csv';

        $reader = SimpleExcelReader::create($path, $tipe)
            ->formatHeadersUsing(fn (string $header): string => $this->normalkanHeader($header));

        if ($tipe === 'csv') {
            $reader->useDelimiter(self::PEMISAH_CSV);
        }

        return $reader->getRows();
    }

    private function normalkanHeader(string $header): string
    {
        return str_replace(' ', '_', strtolower(trim(ltrim($header, "\xEF\xBB\xBF"))));
    }

    private function jabatan(array $baris): string
    {
        foreach (self::KUNCI_JABATAN as $kunci) {
            $nilai = trim((string) ($baris[$kunci] ?? ''));

            if ($nilai !== '') {
                return strtolower($nilai);
            }
        }

        return self::JABATAN_BAWAAN;
    }

    private function nama(array $baris, string $kodeNpk): string
    {
        $nama = trim((string) ($baris['nama'] ?? ''));

        if ($nama !== '') {
            return strtoupper($nama);
        }

        $email = trim((string) ($baris['email'] ?? ''));

        return strtoupper($email === '' ? $kodeNpk : explode('@', $email)[0]);
    }

    private function nilaiAtauNull(mixed $nilai): ?string
    {
        $teks = trim((string) ($nilai ?? ''));

        return $teks === '' ? null : $teks;
    }
}
