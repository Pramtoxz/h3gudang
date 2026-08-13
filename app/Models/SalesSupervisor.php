<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesSupervisor extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_pmo';

    protected $table = 'sales_supervisor';

    protected $fillable = [
        'nama',
        'kode_npk',
        'no_hp',
        'jabatan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function tokoAsSales(): HasMany
    {
        return $this->hasMany(Toko::class, 'fk_sales', 'kode_npk');
    }

    public function tokoAsSupervisor(): HasMany
    {
        return $this->hasMany(Toko::class, 'fk_spv', 'kode_npk');
    }
}
