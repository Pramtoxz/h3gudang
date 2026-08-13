<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    protected $connection = 'pgsql_dms';

    protected $table = 'public.users';

    protected $guarded = ['*'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isIt(): bool
    {
        return in_array($this->attributes['it'] ?? null, ['t', '1', 1, true], true);
    }

    /**
     * Koneksi pgsql_dms bersifat read-only, sehingga fitur "remember me"
     * dimatikan agar Laravel tidak menulis remember_token ke database DMS.
     */
    public function setRememberToken($value): void
    {
    }

    public function getRememberToken(): ?string
    {
        return null;
    }
}
