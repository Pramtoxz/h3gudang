<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder hanya mengisi schema `warehouse` (koneksi bawaan `pgsql`).
 *
 * Schema `pmov2` adalah salinan production dan TIDAK PERNAH di-seed — isinya
 * data sungguhan. Versi lama berkas ini membuat user palsu lewat
 * `User::factory()`, yang berarti menulis ke `pmov2.users`. Itu sudah dihapus.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProjectSeeder::class,
            MenuSeeder::class,
        ]);
    }
}
