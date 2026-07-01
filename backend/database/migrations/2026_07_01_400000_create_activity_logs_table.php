<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('aksi');
            $table->string('model');
            $table->string('model_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['model', 'model_id']);
            $table->index('created_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('status_aktif');
        });

        Schema::table('pengaduan', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });

        Schema::table('surat', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });

        Schema::table('iuran', function (Blueprint $table) {
            $table->index(['bulan', 'status']);
        });

        Schema::table('keuangan', function (Blueprint $table) {
            $table->index(['jenis', 'rt_rw_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::table('keuangan', fn(Blueprint $t) => $t->dropIndex(['jenis', 'rt_rw_id', 'tanggal']));
        Schema::table('iuran', fn(Blueprint $t) => $t->dropIndex(['bulan', 'status']));
        Schema::table('surat', fn(Blueprint $t) => $t->dropIndex(['status', 'created_at']));
        Schema::table('pengaduan', fn(Blueprint $t) => $t->dropIndex(['status', 'created_at']));
        Schema::table('users', fn(Blueprint $t) => $t->dropIndex(['role']));
        Schema::table('users', fn(Blueprint $t) => $t->dropIndex(['status_aktif']));
        Schema::dropIfExists('activity_logs');
    }
};
