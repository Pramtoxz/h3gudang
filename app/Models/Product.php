<?php

namespace App\Models;

use App\Models\PublicSchema\Part;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $connection = 'pgsql_pmo';

    protected $table = 'gambar_part';

    protected $primaryKey = 'kode_part';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'kode_part',
        'nama',
        'deskripsi',
        'gambar',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'kode_part', 'kd_part');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'kode_part', 'kode_part');
    }
}
