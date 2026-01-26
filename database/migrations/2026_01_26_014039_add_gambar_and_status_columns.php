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
    }
};
