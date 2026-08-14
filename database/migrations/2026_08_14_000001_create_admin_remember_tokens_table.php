<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::connection($this->connection)->create('admin_remember_tokens', function (Blueprint $table) {
            /*
             * Remember token user DMS disimpan di sini, bukan di public.users,
             * karena koneksi DMS read-only. Kuncinya email dengan alasan yang
             * sama seperti menu_akses: id auto-increment DMS belum tentu sama
             * saat koneksi berpindah dari dms_clone ke dmsv2 waktu cutover.
             */
            $table->string('email')->primary();
            $table->string('token', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('admin_remember_tokens');
    }
};
