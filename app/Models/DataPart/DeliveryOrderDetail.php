<?php

namespace App\Models\DataPart;

use App\Models\Product;
use App\Models\PublicSchema\Part;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrderDetail extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'data_part.tbldo_detail';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'qty_do' => 'integer',
            'harga' => 'decimal:2',
            'diskon' => 'decimal:2',
            'total_harga' => 'decimal:2',
        ];
    }

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'fk_do', 'no_do');
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
