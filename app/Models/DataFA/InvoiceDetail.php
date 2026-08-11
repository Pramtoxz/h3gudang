<?php

namespace App\Models\DataFA;

use App\Models\Product;
use App\Models\PublicSchema\Part;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDetail extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'data_fa.tblinvoice_dealer_part_detail';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'qty_do' => 'integer',
            'harga' => 'decimal:2',
            'diskon' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'fk_invoice', 'pk_id_dealer_part');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'fk_part', 'kd_part');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'fk_part', 'kode_part');
    }

    protected function subtotal(): Attribute
    {
        return Attribute::get(fn (): int|float => ($this->harga - $this->diskon) * $this->qty_do);
    }
}
