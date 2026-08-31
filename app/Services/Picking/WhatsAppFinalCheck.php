<?php

namespace App\Services\Picking;

use App\Services\WhatsAppGateway;
use Illuminate\Support\Facades\DB;

/**
 * Notifikasi grup WhatsApp saat DO **bundling** selesai final check — penanda
 * bagi tim invoice bahwa part-nya sudah siap.
 *
 * Susunan pesannya dipertahankan persis seperti aplikasi lama: orang membacanya
 * setiap hari di grup, jadi mengubah urutan atau labelnya justru menyulitkan.
 */
class WhatsAppFinalCheck
{
    private const ID_KONFIGURASI = 3;

    public function kirim(string $fkDo): void
    {
        (new WhatsAppGateway(self::ID_KONFIGURASI))->sendToGroup($this->susunPesan($fkDo));
    }

    private function susunPesan(string $fkDo): string
    {
        $do = DB::connection('pgsql_dms')
            ->table('H3.tbl_picking_inoma as p')
            ->leftJoin('H3.tbl_area_channel as c', 'p.fk_dealer', '=', 'c.kode_channel')
            ->where('p.fk_do', $fkDo)
            ->first(['p.fk_dealer', 'p.poli', 'p.keterangan_final', 'c.nama_channel']);

        $bundling = DB::connection('pgsql_dms')
            ->table('public.bundlingh3')
            ->where('fk_do', $fkDo)
            ->first(['fk_so', 'no_picking_list_unit']);

        $totalItem = DB::connection('pgsql_dms')
            ->table('H3.tbl_picking_inoma')
            ->where('fk_do', $fkDo)
            ->count();

        $namaChannel = $do?->nama_channel ?: 'Channel '.($do?->fk_dealer ?? '-');

        return implode("\n", [
            '*NOTIFIKASI FINAL CHECK BUNDLING H1 to H3*',
            '',
            'Final check telah selesai untuk DO bundling berikut:',
            '',
            '*No. DO:* '.$fkDo,
            '*No. SO:* '.($bundling?->fk_so ?: '-'),
            '*No. PLU:* '.($bundling?->no_picking_list_unit ?: '-'),
            '*Channel:* '.$namaChannel,
            '*Total Item:* '.$totalItem.' part',
            '*Jumlah Koli:* '.((int) ($do?->poli ?? 0)),
            '*Keterangan Final:* '.($do?->keterangan_final ?: '-'),
            '*Waktu:* '.now()->format('d/m/Y H:i:s'),
            '',
            'Status: *SIAP PROSES INVOICE PART*',
            'Mohon segera ditindaklanjuti untuk pembuatan invoice.',
        ]);
    }
}
