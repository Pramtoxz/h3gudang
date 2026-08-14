<?php

namespace App\Models\H3;

use Illuminate\Database\Eloquent\Model;

/**
 * Kolom `id` bertipe serial, jadi `$incrementing` harus true — model lama
 * menyetelnya false sehingga objek hasil `create()` tidak tahu id-nya sendiri.
 *
 * Tabelnya punya UNIQUE (fk_do, fk_part, lokasi_part); itu kunci alami yang
 * dipakai sinkronisasi.
 */
class PickingInoma extends Model
{
    public const STATUS_SIAP = 'Ready For Scan';

    public const STATUS_DONE = 'done';

    public const STATUS_FINAL = 'final';

    protected $connection = 'pgsql_dms';

    protected $table = 'H3.tbl_picking_inoma';

    protected $primaryKey = 'id';

    protected $fillable = [
        'tgl_picking_list_part',
        'no_picking_list_part',
        'fk_do',
        'status_picking_list',
        'tujuan',
        'fk_dealer',
        'fk_part',
        'lokasi_part',
        'qty_part',
        'qty_picking',
        'waktu_done',
        'nomor_kotak',
        'user_cek',
        'tgl_final',
        'keterangan_final',
        'poli',
        'keterangan_picking',
    ];

    protected function casts(): array
    {
        return [
            'tgl_picking_list_part' => 'datetime',
            'waktu_done' => 'datetime',
            'tgl_final' => 'datetime',
            'qty_part' => 'integer',
            'qty_picking' => 'integer',
        ];
    }

    public function sudahDiambil(): bool
    {
        return in_array($this->status_picking_list, [self::STATUS_DONE, self::STATUS_FINAL], true);
    }
}
