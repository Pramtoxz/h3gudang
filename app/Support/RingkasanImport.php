<?php

namespace App\Support;

class RingkasanImport
{
    private const JUMLAH_GALAT_DITAMPILKAN = 3;

    public static function pesan(string $label, array $hasil): string
    {
        $pesan = "{$label} berhasil diimport ({$hasil['diproses']} baris)";

        if ($hasil['galat'] === []) {
            return $pesan;
        }

        $ditampilkan = array_slice($hasil['galat'], 0, self::JUMLAH_GALAT_DITAMPILKAN);

        return $pesan.' | '.count($hasil['galat']).' gagal: '.implode(' | ', $ditampilkan);
    }
}
