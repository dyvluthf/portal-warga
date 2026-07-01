<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warga', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->foreignId('rt_id')->nullable()->constrained('rt_rw')->nullOnDelete();
            $table->foreignId('rw_id')->nullable()->constrained('rt_rw')->nullOnDelete();
            $table->string('no_telp', 20)->nullable();
            $table->string('foto_profil')->nullable();
            $table->enum('status_verifikasi', ['menunggu', 'terverifikasi', 'ditolak'])->default('menunggu');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warga');
    }
};
