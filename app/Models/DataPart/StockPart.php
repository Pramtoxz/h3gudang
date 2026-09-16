<?php

namespace App\Models\DataPart;

use App\Models\PublicSchema\Part;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPart extends Model
{
    public const ALLOWED_WAREHOUSES = ['GDG-1', 'GDG-2'];

    protected $connection = 'pgsql_dms';

    protected $table = 'data_part.tblstock_part';

    public $timestamps = false;

    protected $guarded = ['*'];

    public function scopeAllowedWarehouses($query)
    {
        return $query->whereIn('fk_gudang', self::ALLOWED_WAREHOUSES);
    }

    public function scopeForPeriod($query, ?int $bulan = null, ?int $tahun = null)
    {
        $bulan = (int) ($bulan ?? date('n'));
        $tahun = (int) ($tahun ?? date('Y'));

        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'fk_part', 'kd_part');
    }

    protected function available(): Attribute
    {
        return Attribute::get(function (): float {
            $part = $this->part;
            $minStock = ($part && is_numeric($part->min_stok) && (int) $part->min_stok > 0) ? (int) $part->min_stok : 0;

            return ((float) $this->qty_on_hand - (float) $this->qty_booking) - $minStock;
        });
    }

    protected function isAvailable(): Attribute
    {
        return Attribute::get(fn (): bool => $this->available >= 1);
    }
}
