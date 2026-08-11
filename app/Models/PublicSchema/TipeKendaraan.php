<?php

namespace App\Models\PublicSchema;

use Illuminate\Database\Eloquent\Model;

class TipeKendaraan extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'public.tbltipe_kendaraan_id';

    public $timestamps = false;

    protected $guarded = ['*'];
}
