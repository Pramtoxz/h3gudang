<?php

namespace App\Models\DataPart;

use App\Models\PublicSchema\Part;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaanPartScan extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'data_part.tblpenerimaan_part_scan_no_part';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'qty_storing' => 'integer',
        ];
    }

    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(PenerimaanPart::class, 'fk_penerimaan_part_scan', 'no_penerimaan_part');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'fk_part', 'kd_part');
    }
}
