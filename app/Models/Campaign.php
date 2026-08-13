<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_pmo';

    protected $table = 'kampanye';

    protected $fillable = [
        'judul',
        'badge',
        'deskripsi',
        'gambar',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'deskripsi_lengkap',
        'syarat_ketentuan',
        'part_termasuk',
        'hadiah',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'datetime',
            'tanggal_selesai' => 'datetime',
            'part_termasuk' => 'array',
            'hadiah' => 'array',
        ];
    }
}
