<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class AdminUser extends Authenticatable
{
    use HasApiTokens;

    protected $connection = 'pgsql_dms';

    protected $table = 'public.users';

    protected $guarded = ['*'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    private ?string $rememberTokenTersimpan = null;

    private bool $rememberTokenSudahDiambil = false;

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
     * Remember token tidak boleh menyentuh DMS yang read-only, jadi nilainya
     * hidup di `warehouse.admin_remember_tokens`. Yang menuliskannya ke sana
     * adalah App\Auth\AdminUserProvider; di sini token hanya disimpan sementara
     * supaya `SessionGuard` bisa membacanya kembali dalam request yang sama.
     */
    public function setRememberToken($value): void
    {
        $this->rememberTokenTersimpan = $value;
        $this->rememberTokenSudahDiambil = true;
    }

    public function getRememberToken(): ?string
    {
        if (! $this->rememberTokenSudahDiambil) {
            $this->rememberTokenTersimpan = AdminRememberToken::query()
                ->where('email', $this->attributes['email'] ?? '')
                ->value('token');
            $this->rememberTokenSudahDiambil = true;
        }

        return $this->rememberTokenTersimpan;
    }
}
