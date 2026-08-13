<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionCache extends Model
{
    protected $connection = 'pgsql_pmo';

    protected $table = 'collections_cache';

    public $timestamps = false;

    protected $fillable = [
        'kd_toko',
        'tgl_faktur',
        'jenis_pembayaran',
        'no_faktur',
        'fk_do_part',
        'no_so',
        'nilai_faktur',
        'saldo',
        'status',
        'bulan',
        'tahun',
        'cached_at',
    ];

    protected function casts(): array
    {
        return [
            'nilai_faktur' => 'decimal:2',
            'saldo' => 'decimal:2',
            'bulan' => 'integer',
            'tahun' => 'integer',
            'cached_at' => 'datetime',
        ];
    }
}
