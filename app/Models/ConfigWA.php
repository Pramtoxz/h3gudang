<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigWA extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'config_wa';

    protected $guarded = ['*'];
}
