<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->integer('stok_baik')->default(0)->after('stok');
            $table->integer('stok_rusak')->default(0)->after('stok_baik');
        });
    }

    public function down(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->dropColumn(['stok_baik', 'stok_rusak']);
        });
    }
};
