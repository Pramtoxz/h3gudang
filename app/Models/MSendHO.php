<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MSendHO extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'H3.tb_send_ho';

    protected $primaryKey = 'id';

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'tgl_kirim_akhir' => 'date',
        ];
    }
}
