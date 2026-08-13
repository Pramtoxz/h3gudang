<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuAkses extends Model
{
    protected $table = 'menu_akses';

    protected $fillable = [
        'email',
        'menu_id',
        'boleh_lihat',
        'boleh_tambah',
        'boleh_ubah',
        'boleh_hapus',
    ];

    protected function casts(): array
    {
        return [
            'boleh_lihat' => 'boolean',
            'boleh_tambah' => 'boolean',
            'boleh_ubah' => 'boolean',
            'boleh_hapus' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
