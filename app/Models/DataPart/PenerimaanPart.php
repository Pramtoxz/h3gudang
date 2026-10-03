<?php

namespace App\Models\DataPart;

use App\Models\H3\GudangPart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaanPart extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'data_part.tblpenerimaan_part';

    protected $primaryKey = 'no_penerimaan_part';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'tgl_penerimaan_part' => 'datetime',
            'tgl_close_penerimaan' => 'datetime',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(PenerimaanPartScan::class, 'fk_penerimaan_part_scan', 'no_penerimaan_part');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(GudangPart::class, 'fk_gudang', 'kd_gudang_part');
    }
}
