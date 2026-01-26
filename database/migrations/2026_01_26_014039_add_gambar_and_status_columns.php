<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('gambar')->nullable()->after('nomor');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])
                ->default('menunggu')
                ->after('password');
        });

        Schema::table('submission_operational_details', function (Blueprint $table) {
            $table->decimal('harga_dapur', 15, 2)->default(0)->after('harga_satuan');
            $table->decimal('harga_mitra', 15, 2)->nullable()->after('harga_dapur');
            $table->decimal('subtotal_dapur', 15, 2)->default(0)->after('harga_mitra');
            $table->decimal('subtotal_mitra', 15, 2)->nullable()->after('subtotal_dapur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        // Rollback suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('gambar');
        });

        // Rollback users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('submission_operational_details', function (Blueprint $table) {
            $table->dropColumn([
                'harga_dapur',
                'harga_mitra',
                'subtotal_dapur',
                'subtotal_mitra',
            ]);
        });
    }
};
