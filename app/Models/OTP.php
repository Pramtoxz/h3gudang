<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    protected $connection = 'pgsql_pmo';

    protected $table = 'otp_codes';

    protected $fillable = [
        'nohp',
        'otp_code',
        'type',
        'is_used',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_used' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }
}
