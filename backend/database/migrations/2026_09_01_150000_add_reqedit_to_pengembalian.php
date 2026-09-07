<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengembalian', function (Blueprint $table) {
            $table->text('catatan_perbaikan')->nullable()->after('denda');
            $table->boolean('butuh_perbaikan')->default(false)->after('catatan_perbaikan');
            $table->enum('status_perbaikan', ['pending','disetujui','ditolak'])->nullable()->after('butuh_perbaikan');
        });
    }

    public function down(): void
    {
        Schema::table('pengembalian', function (Blueprint $table) {
            $table->dropColumn(['catatan_perbaikan','butuh_perbaikan','status_perbaikan']);
        });
    }
};
