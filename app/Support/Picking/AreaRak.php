<?php

namespace App\Support\Picking;

/**
 * Penentu area rak dari kode lokasi.
 *
 * Urutan aturan menentukan hasil dan tidak boleh ditukar: `GUDANG OLI` harus
 * lebih dulu daripada `F - J` karena `GU` juga cocok `[FGHIJ]`, dan
 * `E & Tools` harus lebih dulu daripada `T` karena `TOOLS` milik `E & Tools`.
 */
final class AreaRak
{
    public const LAINNYA = 'AREA LAIN';

    /**
     * @var array<string, array{pola: string, kecuali: ?string, abaikanHuruf: bool}>
     */
    private const ATURAN = [
        'GUDANG OLI' => ['pola' => '^(GU|OLI)', 'kecuali' => null, 'abaikanHuruf' => true],
        'A1 - A3' => ['pola' => '^A[123]', 'kecuali' => null, 'abaikanHuruf' => false],
        'A4 - A5' => ['pola' => '^A[45]', 'kecuali' => null, 'abaikanHuruf' => false],
        'F - J' => ['pola' => '^[FGHIJ]', 'kecuali' => '^HT', 'abaikanHuruf' => false],
        'C' => ['pola' => '^C', 'kecuali' => null, 'abaikanHuruf' => false],
        'D' => ['pola' => '^D', 'kecuali' => null, 'abaikanHuruf' => false],
        'E & Tools' => ['pola' => '^(E|TOOLS)', 'kecuali' => null, 'abaikanHuruf' => true],
        'B' => ['pola' => '^B', 'kecuali' => null, 'abaikanHuruf' => false],
        'K' => ['pola' => '^K', 'kecuali' => null, 'abaikanHuruf' => false],
        'T' => ['pola' => '^T', 'kecuali' => null, 'abaikanHuruf' => false],
        'HTL & URGENT' => ['pola' => '^(URGENT|HTL)', 'kecuali' => null, 'abaikanHuruf' => false],
    ];

    /**
     * @return list<string>
     */
    public static function daftar(): array
    {
        return [...array_keys(self::ATURAN), self::LAINNYA];
    }

    public static function untuk(?string $kdLokasi): string
    {
        $kode = trim((string) $kdLokasi);

        foreach (self::ATURAN as $area => $aturan) {
            if (self::cocok($kode, $aturan['pola'], $aturan['abaikanHuruf'])
                && ! self::cocok($kode, $aturan['kecuali'], $aturan['abaikanHuruf'])) {
                return $area;
            }
        }

        return self::LAINNYA;
    }

    /**
     * Wajib dipakai untuk SELECT, GROUP BY, penyaringan, maupun hapus massal.
     * Menulis kondisi area sendiri membuat yang terhapus bisa berbeda dari yang
     * tampil di layar — persis bug hapus massal `T` di aplikasi lama.
     */
    public static function ekspresiSql(string $kolom): string
    {
        $cabang = [];

        foreach (self::ATURAN as $area => $aturan) {
            $operator = $aturan['abaikanHuruf'] ? '~*' : '~';
            $syarat = sprintf("%s %s '%s'", $kolom, $operator, $aturan['pola']);

            if ($aturan['kecuali'] !== null) {
                $syarat .= sprintf(" AND %s !%s '%s'", $kolom, $operator, $aturan['kecuali']);
            }

            $cabang[] = sprintf('WHEN %s THEN %s', $syarat, self::kutip($area));
        }

        return sprintf('CASE %s ELSE %s END', implode(' ', $cabang), self::kutip(self::LAINNYA));
    }

    private static function cocok(string $kode, ?string $pola, bool $abaikanHuruf): bool
    {
        if ($pola === null) {
            return false;
        }

        return preg_match('/'.$pola.'/'.($abaikanHuruf ? 'i' : ''), $kode) === 1;
    }

    private static function kutip(string $nilai): string
    {
        return "'".str_replace("'", "''", $nilai)."'";
    }
}
