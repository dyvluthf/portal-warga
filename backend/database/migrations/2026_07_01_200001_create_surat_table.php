<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->string('jenis_surat');
            $table->string('nomor_surat')->nullable()->unique();
            $table->json('data_form');
            $table->json('dokumen_pendukung')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'diterbitkan', 'ditolak'])->default('pending');
            $table->text('alasan_penolakan')->nullable();
            $table->string('file_pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
