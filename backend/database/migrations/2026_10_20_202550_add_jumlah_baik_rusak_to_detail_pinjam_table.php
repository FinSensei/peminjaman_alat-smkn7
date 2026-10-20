<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pinjam', function (Blueprint $table) {
            $table->integer('jumlah_baik')->default(0)->after('jumlah');
            $table->integer('jumlah_rusak')->default(0)->after('jumlah_baik');
        });
    }

    public function down(): void
    {
        Schema::table('detail_pinjam', function (Blueprint $table) {
            $table->dropColumn(['jumlah_baik', 'jumlah_rusak']);
        });
    }
};
