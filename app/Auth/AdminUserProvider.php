<?php

namespace App\Auth;

use App\Models\AdminRememberToken;
use App\Models\AdminUser;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Provider untuk user DMS. Seluruh perilaku Eloquent diwarisi; yang ditimpa
 * hanya tiga jalur yang kalau dibiarkan akan menulis ke `pgsql_dms`, padahal
 * koneksi itu read-only mutlak.
 */
class AdminUserProvider extends EloquentUserProvider
{
    /**
     * Laravel 12 menulis ulang kolom password setiap kredensial terbukti benar
     * bila cost hash-nya berbeda dari BCRYPT_ROUNDS. Di DMS hampir seluruh hash
     * bercost 10 sedangkan aplikasi memakai 12, sehingga tiap login berhasil
     * memicu UPDATE ke public.users. Jalur itu ditutup di sini.
     */
    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false): void
    {
    }

    public function updateRememberToken(Authenticatable $user, #[\SensitiveParameter] $token): void
    {
        $user->setRememberToken($token);

        if (! $user instanceof AdminUser) {
            return;
        }

        AdminRememberToken::query()->updateOrCreate(
            ['email' => $user->email],
            ['token' => $token],
        );
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
    {
        $user = $this->retrieveById($identifier);

        if (! $user instanceof AdminUser) {
            return null;
        }

        $tersimpan = AdminRememberToken::query()
            ->where('email', $user->email)
            ->value('token');

        return $tersimpan && hash_equals($tersimpan, (string) $token) ? $user : null;
    }

    public static function cabutToken(?string $email): void
    {
        if ($email === null) {
            return;
        }

        AdminRememberToken::query()->where('email', $email)->delete();
    }
}
