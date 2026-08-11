<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Toko extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $table = 'pmov2.tbltoko';

    protected $primaryKey = 'kd_toko';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'kd_toko',
        'toko',
        'no_telp',
        'alamat',
        'npwp',
        'kategori',
        'kd_ahm',
        'toko_active',
        'tipe_diskon',
    ];

    protected $appends = ['nama', 'kode', 'aktif', 'no_hp', 'kota', 'provinsi'];

    protected function casts(): array
    {
        return [
            'toko_active' => 'boolean',
        ];
    }

    protected function nama(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->toko);
    }

    protected function kode(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->kd_toko);
    }

    protected function aktif(): Attribute
    {
        return Attribute::get(fn (): ?bool => $this->toko_active);
    }

    protected function noHp(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->no_telp);
    }

    protected function kota(): Attribute
    {
        return Attribute::get(fn (): ?string => null);
    }

    protected function provinsi(): Attribute
    {
        return Attribute::get(fn (): ?string => null);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'fk_toko', 'kd_toko');
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(SalesSupervisor::class, 'fk_sales', 'kode_npk');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(SalesSupervisor::class, 'fk_spv', 'kode_npk');
    }
}
