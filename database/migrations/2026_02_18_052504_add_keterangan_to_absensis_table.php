<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('absensis', function (Blueprint $table) {
        // Kita tambahkan kolom keterangan setelah kolom status
        $table->text('keterangan')->nullable()->after('status');
    });
}

public function down(): void
{
    Schema::table('absensis', function (Blueprint $table) {
        $table->dropColumn('keterangan');
    });
}
};
