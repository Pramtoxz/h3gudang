<?php

namespace App\Models;

use App\Models\PublicSchema\Part;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'pmov2.item_keranjang';

    protected $fillable = [
        'keranjang_id',
        'kode_part',
        'qty',
        'harga',
        'diskon',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga' => 'decimal:2',
            'diskon' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (CartItem $item): void {
            $item->subtotal = ($item->harga * $item->qty) - $item->diskon;
        });
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'keranjang_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'kode_part', 'kode_part');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'kode_part', 'kd_part');
    }
}
