<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('konten');
            $table->string('kategori')->nullable();
            $table->string('gambar')->nullable();
            $table->enum('status', ['draft', 'publish'])->default('draft');
            $table->foreignId('penulis_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('tanggal_publish')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};
