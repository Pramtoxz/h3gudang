<?php

namespace App\Services\Picking;

use App\Models\H3\PickingInoma;
use Illuminate\Support\Facades\DB;

class SinkronisasiDoService
{
    private const UKURAN_POTONGAN = 200;

    private const KUNCI = ['fk_do', 'fk_part', 'lokasi_part'];

    private const KOLOM_DIPERBARUI = [
        'tgl_picking_list_part',
        'no_picking_list_part',
        'status_picking_list',
        'tujuan',
        'fk_dealer',
        'qty_part',
        'qty_picking',
        'keterangan_picking',
    ];

    /**
     * @return array{dibaca: int, dilewati: int, disimpan: int}
     */
    public function jalankan(): array
    {
        $sumber = $this->ambilDariDms();
        $terkunci = $this->kunciYangSudahDiambil($this->daftarDo($sumber));

        $baris = [];
        $dilewati = 0;

        foreach ($sumber as $item) {
            if (! $this->layak($item)) {
                $dilewati++;

                continue;
            }

            $kunci = $this->kunciDari($item);

            if (isset($terkunci[$this->gabung($kunci)])) {
                $dilewati++;

                continue;
            }

            $baris[$this->gabung($kunci)] = [...$kunci, ...$this->atribut($item)];
        }

        foreach (array_chunk(array_values($baris), self::UKURAN_POTONGAN) as $potongan) {
            PickingInoma::upsert($potongan, self::KUNCI, self::KOLOM_DIPERBARUI);
        }

        return [
            'dibaca' => count($sumber),
            'dilewati' => $dilewati,
            'disimpan' => count($baris),
        ];
    }

    private function ambilDariDms(): array
    {
        return DB::connection('pgsql_dms')->select(<<<'SQL'
            SELECT
                a.tgl_picking_list_part, a.no_picking_list_part, a.fk_do,
                a.status_picking_list, a.tujuan, a.fk_dealer,
                c.fk_part, c.lokasi_part, c.qty_part,
                b.qty_picking, d.keterangan AS keterangan_do
            FROM data_part.tblpicking_list_part a
            LEFT JOIN data_part.tblpicking_list_part_detail b
                ON a.no_picking_list_part = b.fk_picking_list_part
            LEFT JOIN data_part.tbllokasi_picking_list c
                ON a.fk_do = c.fk_do AND c.fk_picking_list = a.no_picking_list_part
            LEFT JOIN data_part.tbldo d
                ON a.fk_do = d.no_do
            WHERE a.alasan_batal IS NULL
              AND a.status_picking_list = 'Ready For Scan'
            ORDER BY a.tgl_picking_list_part DESC, a.no_picking_list_part
        SQL);
    }

    /**
     * Baris yang sudah ditandai done atau final tidak boleh ditimpa sync —
     * itu hasil kerja operator. Pencariannya dibatasi DO yang sedang aktif
     * saja, bukan seluruh 55 ribu baris tabel.
     *
     * @param  list<string>  $daftarDo
     * @return array<string, true>
     */
    private function kunciYangSudahDiambil(array $daftarDo): array
    {
        if ($daftarDo === []) {
            return [];
        }

        $terkunci = [];

        PickingInoma::query()
            ->whereIn('fk_do', $daftarDo)
            ->whereIn('status_picking_list', [PickingInoma::STATUS_DONE, PickingInoma::STATUS_FINAL])
            ->select(self::KUNCI)
            ->cursor()
            ->each(function (PickingInoma $baris) use (&$terkunci): void {
                $terkunci[$this->gabung([
                    'fk_do' => $baris->fk_do,
                    'fk_part' => $baris->fk_part,
                    'lokasi_part' => $baris->lokasi_part,
                ])] = true;
            });

        return $terkunci;
    }

    /**
     * @return list<string>
     */
    private function daftarDo(array $sumber): array
    {
        return collect($sumber)
            ->map(fn (object $item): string => $this->bersihkan($item->fk_do))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function layak(object $item): bool
    {
        return $this->bersihkan($item->no_picking_list_part) !== ''
            && $this->bersihkan($item->fk_do) !== ''
            && $this->bersihkan($item->fk_part) !== '';
    }

    /**
     * @return array{fk_do: string, fk_part: string, lokasi_part: string}
     */
    private function kunciDari(object $item): array
    {
        return [
            'fk_do' => $this->bersihkan($item->fk_do),
            'fk_part' => $this->bersihkan($item->fk_part),
            'lokasi_part' => $this->bersihkan($item->lokasi_part),
        ];
    }

    private function atribut(object $item): array
    {
        return [
            'tgl_picking_list_part' => $item->tgl_picking_list_part,
            'no_picking_list_part' => $this->bersihkan($item->no_picking_list_part),
            'status_picking_list' => trim((string) $item->status_picking_list),
            'tujuan' => $this->bersihkan($item->tujuan),
            'fk_dealer' => $this->bersihkan($item->fk_dealer),
            'qty_part' => (int) ($item->qty_part ?: 0),
            'qty_picking' => (int) ($item->qty_picking ?: 0),
            'keterangan_picking' => isset($item->keterangan_do) ? trim((string) $item->keterangan_do) : null,
        ];
    }

    private function bersihkan(mixed $nilai): string
    {
        return strtoupper(trim((string) $nilai));
    }

    private function gabung(array $kunci): string
    {
        return implode('|', $kunci);
    }
}
