<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'pmov2.keranjang';

    protected $fillable = [
        'user_id',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class, 'keranjang_id');
    }

    protected function total(): Attribute
    {
        return Attribute::get(fn () => $this->items->sum('subtotal'));
    }

    protected function totalItems(): Attribute
    {
        return Attribute::get(fn () => $this->items->sum('qty'));
    }
}
