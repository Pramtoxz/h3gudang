<?php

namespace App\Models\PublicSchema;

use App\Models\DataPart\StockPart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Part extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'public.tblpart';

    protected $primaryKey = 'kd_part';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'het' => 'decimal:2',
            'min_stok' => 'integer',
            'part_active' => 'boolean',
        ];
    }

    public function stock(): HasMany
    {
        return $this->hasMany(StockPart::class, 'fk_part', 'kd_part');
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'kode_part', 'kd_part');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class, 'fk_detail_sub_kelompok_part', 'kd_detail_sub_kelompok_part');
    }
}
