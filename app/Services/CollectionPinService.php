<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CollectionPinService
{
    public function sudahDiatur(User $user): bool
    {
        return ! empty($user->collection_pin);
    }

    public function atur(User $user, string $pin): void
    {
        $user->collection_pin = Hash::make($pin);
        $user->save();
    }

    public function cocok(User $user, string $pin): bool
    {
        return $this->sudahDiatur($user) && Hash::check($pin, $user->collection_pin);
    }
}
