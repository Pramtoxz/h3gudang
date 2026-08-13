<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::connection($this->connection)->create('menus', function (Blueprint $table) {
            $table->id();

            /* project_id NULL berarti menu global: tampil di semua project. */
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
            $table->string('nama_menu');
            $table->string('ikon')->nullable();
            $table->string('route')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->cascadeOnDelete();
            $table->integer('urutan')->default(0);
            $table->boolean('status_aktif')->default(true);

            /* khusus_it = true: tidak pernah bisa dicentangkan ke user biasa. */
            $table->boolean('khusus_it')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('menus');
    }
};
