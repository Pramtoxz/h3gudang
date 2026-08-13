<?php

namespace App\Models;

use App\Models\PublicSchema\Part;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopularPart extends Model
{
    protected $connection = 'pgsql_pmo';

    protected $table = 'part_populer';

    protected $fillable = [
        'kode_part',
        'total_qty_terjual',
        'total_order',
        'total_omzet',
        'peringkat',
        'tanggal_generate',
    ];

    protected function casts(): array
    {
        return [
            'total_omzet' => 'decimal:2',
            'tanggal_generate' => 'datetime',
        ];
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'kode_part', 'kd_part');
    }
}
