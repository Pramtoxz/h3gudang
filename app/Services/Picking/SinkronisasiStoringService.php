<?php

namespace App\Services\Picking;

use App\Models\H3\KartuStock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SinkronisasiStoringService
{
    private const UKURAN_POTONGAN = 200;

    /**
     * @return array{dibaca: int, disimpan: int, diperbarui: int, dilewati: int}
     */
    public function jalankan(int $hari = 30): array
    {
        $startDate = Carbon::now()->subDays($hari);
        $now = Carbon::now();
        $tglKartuDefault = $now->format('Y-m-d');

        $existingData = DB::connection('pgsql_dms')
            ->table('H3.kartustok')
            ->whereDate('created_at', '>=', $startDate)
            ->where('fk_do', 'LIKE', '%RS%')
            ->select('fk_do', 'no_part', 'kode_rak', 'status_masuk')
            ->get();

        $existingKeys = [];
        foreach ($existingData as $item) {
            $key = $item->fk_do.'|'.$item->no_part.'|'.$item->kode_rak;
            $existingKeys[$key] = (bool) $item->status_masuk;
        }

        $sumber = DB::connection('pgsql_dms')
            ->table('data_part.tblpenerimaan_part_scan_no_part as detail')
            ->join('data_part.tblpenerimaan_part as header', 'detail.fk_penerimaan_part_scan', '=', 'header.no_penerimaan_part')
            ->where('header.tgl_penerimaan_part', '>=', $startDate)
            ->where('header.no_penerimaan_part', 'LIKE', '%RS%')
            ->whereNotNull('detail.qty_storing')
            ->where('detail.qty_storing', '>', 0)
            ->whereNotNull('detail.no_doos')
            ->select([
                'detail.fk_penerimaan_part_scan',
                'detail.fk_part',
                'detail.fk_lokasi_part',
                'detail.qty_storing',
                'detail.no_doos',
                'header.fk_gudang',
                'header.status_terima',
            ])
            ->cursor();

        $rowsToInsert = [];
        $dibaca = 0;
        $disimpan = 0;
        $diperbarui = 0;
        $dilewati = 0;

        DB::connection('pgsql_dms')->beginTransaction();

        try {
            foreach ($sumber as $part) {
                $dibaca++;
                $isAlreadyClosed = ($part->status_terima === 'Close');
                $cekKey = $part->fk_penerimaan_part_scan.'|'.$part->fk_part.'|'.$part->fk_lokasi_part;

                if (array_key_exists($cekKey, $existingKeys)) {
                    $statusMasukSaatIni = $existingKeys[$cekKey];

                    if (! $statusMasukSaatIni && $isAlreadyClosed) {
                        DB::connection('pgsql_dms')
                            ->table('H3.kartustok')
                            ->where('fk_do', $part->fk_penerimaan_part_scan)
                            ->where('no_part', $part->fk_part)
                            ->where('kode_rak', $part->fk_lokasi_part)
                            ->update([
                                'status_masuk' => true,
                                'qty_masuk' => $part->qty_storing,
                                'updated_at' => $now,
                            ]);
                        $diperbarui++;
                    } else {
                        $dilewati++;
                    }
                    continue;
                }

                $rowsToInsert[] = [
                    'fk_do' => $part->fk_penerimaan_part_scan,
                    'no_part' => $part->fk_part,
                    'kode_rak' => $part->fk_lokasi_part,
                    'qty_diterima' => (int) $part->qty_storing,
                    'no_doos' => $part->no_doos,
                    'fk_gudang' => $part->fk_gudang,
                    'status_masuk' => $isAlreadyClosed,
                    'qty_masuk' => $isAlreadyClosed ? (int) $part->qty_storing : null,
                    'tgl_kartu' => $tglKartuDefault,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $disimpan++;

                if (count($rowsToInsert) >= self::UKURAN_POTONGAN) {
                    DB::connection('pgsql_dms')->table('H3.kartustok')->insert($rowsToInsert);
                    $rowsToInsert = [];
                }
            }

            if (! empty($rowsToInsert)) {
                DB::connection('pgsql_dms')->table('H3.kartustok')->insert($rowsToInsert);
            }

            DB::connection('pgsql_dms')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql_dms')->rollBack();
            Log::error('Sinkronisasi Storing Gagal: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }

        return [
            'dibaca' => $dibaca,
            'disimpan' => $disimpan,
            'diperbarui' => $diperbarui,
            'dilewati' => $dilewati,
        ];
    }
}
