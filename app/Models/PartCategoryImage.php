<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartCategoryImage extends Model
{
    protected $table = 'pmov2.gambar_kelompok_part';

    protected $fillable = [
        'kode_kelompok',
        'nama_kelompok',
        'gambar',
        'deskripsi',
    ];
}
