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
        Schema::table('sells', function (Blueprint $table) {
            // Hapus kolom harga_mitra dan harga_dapur
            $table->dropColumn(['harga_mitra', 'harga_dapur']);
        });

        Schema::table('sells', function (Blueprint $table) {
            // Tambahkan kolom harga baru
            $table->double('harga')->after('bobot_jumlah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sells', function (Blueprint $table) {
            // Hapus kolom harga
            $table->dropColumn('harga');
        });

        Schema::table('sells', function (Blueprint $table) {
            // Kembalikan kolom harga_mitra dan harga_dapur
            $table->double('harga_mitra')->after('bobot_jumlah');
            $table->double('harga_dapur')->after('harga_mitra');
        });
    }
};
