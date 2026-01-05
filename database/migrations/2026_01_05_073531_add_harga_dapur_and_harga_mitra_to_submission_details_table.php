<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('submission_details', function (Blueprint $table) {
            $table->decimal('harga_dapur', 15, 2)->nullable()->after('harga_satuan_saat_itu');
            $table->decimal('harga_mitra', 15, 2)->nullable()->after('harga_dapur');
        });

        // Update data yang sudah ada: set harga_dapur dan harga_mitra dari harga_satuan_saat_itu
        DB::table('submission_details')
            ->whereNull('harga_dapur')
            ->orWhereNull('harga_mitra')
            ->update([
                'harga_dapur' => DB::raw('harga_satuan_saat_itu'),
                'harga_mitra' => DB::raw('harga_satuan_saat_itu'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_details', function (Blueprint $table) {
            $table->dropColumn(['harga_dapur', 'harga_mitra']);
        });
    }
};
