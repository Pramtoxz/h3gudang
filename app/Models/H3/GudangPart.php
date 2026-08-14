<?php

namespace App\Models\H3;

use Illuminate\Database\Eloquent\Model;

class GudangPart extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'H3.tblgudang_part';

    protected $primaryKey = 'kd_gudang_part';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'gudang_part_active' => 'boolean',
        ];
    }
}
