<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Token API mobile (Sanctum). Ini tabel sistem, bukan data bisnis, jadi
 * tempatnya di schema `warehouse` bersama session dan cache.
 *
 * Kolom `tokenable_type` berisi string `App\Models\User`. Nama kelas itu
 * TIDAK BOLEH diubah — lihat md/06-KONVENSI-KODE.md §3.
 */
return new class extends Migration
{
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::connection($this->connection)->create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('personal_access_tokens');
    }
};
