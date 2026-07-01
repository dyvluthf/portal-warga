<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iuran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->string('bulan', 7);
            $table->decimal('nominal', 12, 2);
            $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas'])->default('belum_bayar');
            $table->enum('metode', ['manual', 'qris'])->nullable();
            $table->string('bukti_transfer')->nullable();
            $table->timestamp('tanggal_bayar')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_url')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();

            $table->unique(['warga_id', 'bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iuran');
    }
};
