<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRememberToken extends Model
{
    protected $table = 'admin_remember_tokens';

    protected $primaryKey = 'email';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'email',
        'token',
    ];

    protected $hidden = [
        'token',
    ];
}
