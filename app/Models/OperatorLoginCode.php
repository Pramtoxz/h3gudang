<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
 * @property string $kode
 * @property string $created_by_email
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class OperatorLoginCode extends Model
{
    protected $table = 'operator_login_codes';

    protected $fillable = [
        'email',
        'kode',
        'created_by_email',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereNull('used_at')
            ->where('expires_at', '>', now('Asia/Jakarta'));
    }

    public function isAktif(): bool
    {
        return $this->used_at === null && $this->expires_at->setTimezone('Asia/Jakarta')->isFuture();
    }
}