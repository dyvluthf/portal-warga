<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warga', function (Blueprint $table) {
            $table->foreignId('rt_rw_id')->nullable()->after('rw_id')->constrained('rt_rw')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('warga', function (Blueprint $table) {
            $table->dropForeign(['rt_rw_id']);
            $table->dropColumn('rt_rw_id');
        });
    }
};
