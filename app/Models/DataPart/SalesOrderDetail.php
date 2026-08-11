<?php

namespace App\Models\DataPart;

use App\Models\Product;
use App\Models\PublicSchema\Part;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderDetail extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'data_part.tblso_detail';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Ditulis bersama header Sales Order saat checkout PMO.
     */
    protected $fillable = [
        'fk_so',
        'fk_part',
        'harga',
        'qty_so',
        'total_harga',
        'qty_sisa',
        'fk_tipe',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'total_harga' => 'decimal:2',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'fk_so', 'no_so');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'fk_part', 'kd_part');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'fk_part', 'kode_part');
    }
}
