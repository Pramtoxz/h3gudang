<?php

namespace App\Models\H3;

use Illuminate\Database\Eloquent\Model;

class KartuStock extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'H3.kartustok';

    protected $fillable = [
        'fk_do',
        'fk_dealer',
        'tgl_kartu',
        'no_part',
        'kode_rak',
        'qty_masuk',
        'qty_keluar',
        'qty_diterima',
        'status_masuk',
        'no_doos',
        'fk_gudang',
    ];

    protected function casts(): array
    {
        return [
            'tgl_kartu' => 'date',
            'qty_masuk' => 'integer',
            'qty_keluar' => 'integer',
            'qty_diterima' => 'integer',
            'status_masuk' => 'boolean',
        ];
    }
}
