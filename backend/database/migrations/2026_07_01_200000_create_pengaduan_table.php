<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->string('kategori');
            $table->text('deskripsi');
            $table->json('foto')->nullable();
            $table->string('lokasi_lat')->nullable();
            $table->string('lokasi_lng')->nullable();
            $table->text('lokasi_manual')->nullable();
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru');
            $table->text('tanggapan_admin')->nullable();
            $table->tinyInteger('rating')->nullable()->unsigned();
            $table->text('ulasan')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();
        });

        Schema::create('pengaduan_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengaduan_id')->constrained('pengaduan')->cascadeOnDelete();
            $table->string('status');
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaduan_timeline');
        Schema::dropIfExists('pengaduan');
    }
};
