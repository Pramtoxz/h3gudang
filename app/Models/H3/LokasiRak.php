<?php

namespace App\Models\H3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabelnya tidak punya kolom `id`, jadi `$primaryKey` wajib ada — tanpa itu
 * Ubah dan Hapus menghasilkan `WHERE id = ?` dan ditolak PostgreSQL.
 *
 * `kapasitas` bertipe text, jadi sengaja tidak di-cast integer.
 */
class LokasiRak extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'H3.tbllokasi_part';

    protected $primaryKey = 'kd_lokasi';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'kd_lokasi',
        'fk_gudang_part',
        'jenis_lokasi',
        'kapasitas',
        'lokasi_part_active',
    ];

    protected function casts(): array
    {
        return [
            'lokasi_part_active' => 'boolean',
        ];
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(GudangPart::class, 'fk_gudang_part', 'kd_gudang_part');
    }
}
