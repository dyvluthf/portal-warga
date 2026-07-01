<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rt_rw', function (Blueprint $table) {
            $table->id();
            $table->string('rt');
            $table->string('rw');
            $table->string('nama_kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rt_rw');
    }
};
