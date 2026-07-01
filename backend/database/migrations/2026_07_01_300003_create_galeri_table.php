<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeri_album', function (Blueprint $table) {
            $table->id();
            $table->string('nama_album');
            $table->foreignId('rt_rw_id')->constrained('rt_rw')->cascadeOnDelete();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('galeri_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained('galeri_album')->cascadeOnDelete();
            $table->string('path_foto');
            $table->string('caption')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri_foto');
        Schema::dropIfExists('galeri_album');
    }
};
