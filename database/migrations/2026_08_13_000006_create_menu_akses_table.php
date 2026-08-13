<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::connection($this->connection)->create('menu_akses', function (Blueprint $table) {
            $table->id();

            /*
             * Kunci ke user DMS memakai email karena hanya kolom itu yang terbukti
             * unik dan selalu terisi di public.users (480/480). `id` tidak dipakai
             * karena auto-increment belum tentu sama saat koneksi berpindah dari
             * dms_clone ke dmsv2 waktu cutover.
             */
            $table->string('email')->index();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();

            /* boleh_lihat = false sama artinya dengan tidak punya akses sama sekali. */
            $table->boolean('boleh_lihat')->default(true);
            $table->boolean('boleh_tambah')->default(false);
            $table->boolean('boleh_ubah')->default(false);
            $table->boolean('boleh_hapus')->default(false);
            $table->timestamps();

            $table->unique(['email', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('menu_akses');
    }
};
