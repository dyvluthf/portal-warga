<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rt_rw', function (Blueprint $table) {
            $table->renameColumn('rt', 'nomor_rt');
            $table->renameColumn('rw', 'nomor_rw');
            $table->foreignId('ketua_rt_id')->nullable()->constrained('warga')->nullOnDelete();
            $table->foreignId('sekretaris_id')->nullable()->constrained('warga')->nullOnDelete();
            $table->foreignId('bendahara_id')->nullable()->constrained('warga')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rt_rw', function (Blueprint $table) {
            $table->dropForeign(['ketua_rt_id']);
            $table->dropForeign(['sekretaris_id']);
            $table->dropForeign(['bendahara_id']);
            $table->dropColumn(['ketua_rt_id', 'sekretaris_id', 'bendahara_id']);
            $table->renameColumn('nomor_rt', 'rt');
            $table->renameColumn('nomor_rw', 'rw');
        });
    }
};
