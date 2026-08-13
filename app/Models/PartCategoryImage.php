<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartCategoryImage extends Model
{
    protected $connection = 'pgsql_pmo';

    protected $table = 'gambar_kelompok_part';

    protected $fillable = [
        'kode_kelompok',
        'nama_kelompok',
        'gambar',
        'deskripsi',
    ];
}
