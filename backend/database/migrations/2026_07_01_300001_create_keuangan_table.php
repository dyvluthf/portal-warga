<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keuangan', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->string('judul');
            $table->decimal('jumlah', 12, 2);
            $table->string('kategori')->nullable();
            $table->string('bukti_foto')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('rt_rw_id')->constrained('rt_rw')->cascadeOnDelete();
            $table->date('tanggal');
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keuangan');
    }
};
