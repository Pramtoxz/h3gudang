<?php

namespace App\Models\H3;

use Illuminate\Database\Eloquent\Model;

/**
 * Menentukan baris mana yang boleh dilihat operator, bukan halaman mana yang
 * boleh dibuka. Sumbu itu terpisah dan ada di `warehouse.menu_akses`.
 */
class AksesArea extends Model
{
    public const LEVEL_ADMIN = 1;

    public const LEVEL_PIC = 2;

    protected $connection = 'pgsql_dms';

    protected $table = 'H3.tbl_akses_mu';

    protected $primaryKey = 'email';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'email',
        'username',
        'area',
        'level',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
        ];
    }

    public function melihatSemuaArea(): bool
    {
        return $this->level === self::LEVEL_ADMIN;
    }
}
