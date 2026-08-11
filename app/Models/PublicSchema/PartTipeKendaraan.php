<?php

namespace App\Models\PublicSchema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartTipeKendaraan extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'public.tblpart_detail_tipe_kendaraan';

    public $timestamps = false;

    protected $guarded = ['*'];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'fk_part', 'kd_part');
    }
}
