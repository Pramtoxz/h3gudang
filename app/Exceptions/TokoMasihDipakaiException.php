<?php

namespace App\Exceptions;

use RuntimeException;

class TokoMasihDipakaiException extends RuntimeException
{
    public function __construct(public readonly int $jumlahUser)
    {
        parent::__construct("Tidak dapat menghapus toko. Masih ada {$jumlahUser} user yang terdaftar.");
    }
}
