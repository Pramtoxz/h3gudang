<?php

namespace App\Models\DataPart;

use App\Models\PublicSchema\Part;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPart extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'data_part.tblstock_part';

    public $timestamps = false;

    protected $guarded = ['*'];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'fk_part', 'kd_part');
    }

    protected function available(): Attribute
    {
        return Attribute::get(function (): int|float {
            $minStock = $this->part?->min_stok ?? 0;

            return ($this->qty_on_hand - $this->qty_booking) - $minStock;
        });
    }

    protected function isAvailable(): Attribute
    {
        return Attribute::get(fn (): bool => $this->available >= 1);
    }
}
