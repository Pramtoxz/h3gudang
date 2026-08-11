<?php

namespace App\Support;

use Spatie\SimpleExcel\SimpleExcelWriter;

class PenulisSheetExcel
{
    /**
     * Header hanya perlu ditulis manual saat tidak ada baris data, karena
     * penulis mengambil header dari kunci baris pertama.
     */
    public static function tulis(SimpleExcelWriter $writer, array $header, array $baris): void
    {
        if ($baris === []) {
            $writer->addHeader($header);

            return;
        }

        $writer->addRows($baris);
    }
}
