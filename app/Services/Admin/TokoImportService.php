<?php

namespace App\Services\Admin;

use App\Repositories\TokoRepository;
use Illuminate\Support\LazyCollection;
use Spatie\SimpleExcel\SimpleExcelReader;
use Throwable;

class TokoImportService
{
    /**
     * Hash password default dipertahankan persis seperti @old supaya password
     * awal yang sudah diketahui admin tidak berubah untuk toko baru.
     */
    private const PASSWORD_HASH_DEFAULT = '$2y$10$7g7i7KLU4DFMVJLQ24ucfe/tjZ/gVRn6WYi7CGrlbQp6VZO2d.QFW';

    private const DOMAIN_EMAIL_DEFAULT = '@pmo.com';

    private const PEMISAH_CSV = ';';

    private const NILAI_STATUS_AKTIF = ['AKTIF', '1', 'TRUE', 'YES', 'Y'];

    private const BAWAAN_TOKO_BARU = [
        'alamat' => '-',
        'npwp' => '-',
        'tipe_diskon' => TokoService::TIPE_DISKON_DEFAULT,
        'plafon_part' => 0,
        'top_part' => 0,
        'kategori' => 'CHANNEL',
        'up_pemilik' => '-',
        'head_toko' => '',
        'diskon_fix_order' => 0,
        'diskon_regular' => 0,
        'diskon_hotline' => 0,
        'diskon_urgent' => 0,
        'top_oli' => 0,
        'jenis_dealer' => 'H3',
        'toko_cabang' => 'TIDAK',
    ];

    public function __construct(private readonly TokoRepository $tokoRepository)
    {
    }

    public function proses(string $path, string $ekstensi): array
    {
        $diproses = 0;
        $galat = [];

        foreach ($this->bacaBaris($path, $ekstensi) as $baris) {
            $kdToko = trim((string) ($baris['kode'] ?? ''));

            if ($kdToko === '') {
                continue;
            }

            try {
                $this->simpanBaris($kdToko, $baris);
                $diproses++;
            } catch (Throwable $e) {
                $galat[] = "Baris kode {$kdToko}: {$e->getMessage()}";
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

    private function simpanBaris(string $kdToko, array $baris): void
    {
        $namaToko = trim((string) ($baris['nama_toko'] ?? '')) ?: 'Unknown';

        $dataToko = [
            'toko' => $namaToko,
            'fk_sales' => $this->nilaiAtauNull($baris['salesman'] ?? null),
            'fk_spv' => $this->nilaiAtauNull($baris['spv'] ?? null),
            'no_telp' => $this->nilaiAtauNull($baris['nohp'] ?? null),
            'toko_active' => $this->statusAktif($baris['status'] ?? null),
        ];

        if (! $this->tokoRepository->tokoAda($kdToko)) {
            $dataToko = [...$dataToko, ...self::BAWAAN_TOKO_BARU, 'kd_ahm' => $kdToko];
        }

        $this->tokoRepository->simpanTokoDariImport($kdToko, $dataToko);
        $this->sinkronkanUser($kdToko, $namaToko, $baris);
    }

    private function sinkronkanUser(string $kdToko, string $namaToko, array $baris): void
    {
        $email = $this->nilaiAtauNull($baris['email'] ?? null)
            ?? strtolower($kdToko).self::DOMAIN_EMAIL_DEFAULT;

        if ($this->tokoRepository->emailSudahDipakai($email)) {
            $this->tokoRepository->perbaruiUserToko($email, [
                'name' => $namaToko,
                'role' => TokoRepository::ROLE_DEALER,
                'fk_toko' => $kdToko,
            ]);

            return;
        }

        $this->tokoRepository->buatUserToko($email, [
            'name' => $namaToko,
            'password' => self::PASSWORD_HASH_DEFAULT,
            'role' => TokoRepository::ROLE_DEALER,
            'fk_toko' => $kdToko,
        ]);
    }

    private function statusAktif(mixed $nilai): bool
    {
        if ($nilai === null || trim((string) $nilai) === '') {
            return true;
        }

        return in_array(strtoupper(trim((string) $nilai)), self::NILAI_STATUS_AKTIF, true);
    }

    private function nilaiAtauNull(mixed $nilai): ?string
    {
        $teks = trim((string) ($nilai ?? ''));

        return $teks === '' ? null : $teks;
    }
}
