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
        // Drop foreign key constraint dulu
        Schema::table('sells', function (Blueprint $table) {
            $table->dropForeign(['recipe_bahan_baku_id']);
        });

        Schema::table('sells', function (Blueprint $table) {
            $table->string('kode')->unique()->nullable()->after('id');
            $table->date('tanggal')->nullable()->after('kode');
            $table->enum('tipe', ['dapur', 'mitra'])->default('dapur')->after('tanggal');
            $table->foreignId('kitchen_id')->nullable()->constrained('kitchens')->onDelete('cascade')->after('tipe');
            $table->foreignId('bahan_baku_id')->nullable()->constrained('bahan_baku')->onDelete('cascade')->after('recipe_bahan_baku_id');
            $table->foreignId('satuan_id')->nullable()->constrained('units')->onDelete('cascade')->after('bahan_baku_id');
            // Ubah recipe_bahan_baku_id menjadi nullable dan tambahkan kembali foreign key
            $table->foreignId('recipe_bahan_baku_id')->nullable()->change();
        });

        // Tambahkan kembali foreign key constraint
        Schema::table('sells', function (Blueprint $table) {
            $table->foreign('recipe_bahan_baku_id')->references('id')->on('recipe_bahan_baku')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sells', function (Blueprint $table) {
            $table->dropForeign(['kitchen_id']);
            $table->dropForeign(['bahan_baku_id']);
            $table->dropForeign(['satuan_id']);
            $table->dropColumn(['kode', 'tanggal', 'tipe', 'kitchen_id', 'bahan_baku_id', 'satuan_id']);
        });
    }
};
