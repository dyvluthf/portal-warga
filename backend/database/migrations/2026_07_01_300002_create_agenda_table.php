<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->date('tanggal');
            $table->time('jam');
            $table->string('lokasi');
            $table->text('deskripsi')->nullable();
            $table->foreignId('rt_rw_id')->constrained('rt_rw')->cascadeOnDelete();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('agenda_kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->constrained('agenda')->cascadeOnDelete();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->enum('status_rsvp', ['hadir', 'tidak_hadir']);
            $table->timestamps();

            $table->unique(['agenda_id', 'warga_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_kehadiran');
        Schema::dropIfExists('agenda');
    }
};
