<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PublicSchema\Part;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;

class CartService
{
    public function keranjangAktif(User $user): ?Cart
    {
        return $user->activeCart()->with(['items.part.stock', 'items.product'])->first();
    }

    public function tambahItem(User $user, string $partNumber, int $jumlah): array
    {
        $part = Part::where('kd_part', $partNumber)->firstOrFail();

        if (! $part->part_active) {
            throw new RuntimeException(
                'Part ini sudah tidak diproduksi (discontinued) dan tidak bisa dipesan. Hanya untuk referensi harga.'
            );
        }

        $keranjang = $user->activeCart()->firstOrCreate([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        if ($keranjang->items()->where('kode_part', $part->kd_part)->exists()) {
            throw new RuntimeException('Part sudah ada di keranjang');
        }

        $item = $keranjang->items()->create([
            'kode_part' => $part->kd_part,
            'qty' => $jumlah,
            'harga' => $part->het,
            'diskon' => 0,
        ]);

        $siap = $part->stock()->first()?->is_available ?? false;

        return [
            'cartItemId' => (string) $item->id,
            'totalItems' => $keranjang->fresh()->totalItems,
            'isReady' => $siap,
            'message' => $siap ? 'Item added to cart' : 'Pre-order item added to cart',
        ];
    }

    public function ubahJumlah(User $user, int|string $itemId, int $jumlah): void
    {
        $item = $this->cariItemMilikUser($user, $itemId);

        $item->qty = $jumlah;
        $item->save();
    }

    public function hapusItem(User $user, int|string $itemId): void
    {
        $this->cariItemMilikUser($user, $itemId)->delete();
    }

    public function kosongkan(User $user): void
    {
        $user->activeCart()->first()?->items()->delete();
    }

    /**
     * @throws ModelNotFoundException
     */
    private function cariItemMilikUser(User $user, int|string $itemId): CartItem
    {
        return CartItem::whereHas('cart', function ($query) use ($user): void {
            $query->where('user_id', $user->id)->where('status', 'active');
        })->findOrFail($itemId);
    }
}
