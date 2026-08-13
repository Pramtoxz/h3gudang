<?php

namespace App\Models\H3;

use Illuminate\Database\Eloquent\Model;

class AreaChannel extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'H3.tbl_area_channel';

    public $timestamps = false;

    protected $fillable = [
        'area',
        'kode_channel',
        'nama_channel',
        'nama_invoice',
    ];
}
