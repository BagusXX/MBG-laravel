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
        Schema::table('submission_details', function (Blueprint $table) {
            $table->dropForeign(['recipe_bahan_baku_id']);
        });

        // Ubah kolom menjadi nullable
        Schema::table('submission_details', function (Blueprint $table) {
            $table->foreignId('recipe_bahan_baku_id')->nullable()->change();
        });

        // Tambahkan kembali foreign key constraint dengan nullable
        Schema::table('submission_details', function (Blueprint $table) {
            $table->foreign('recipe_bahan_baku_id')->references('id')->on('recipe_bahan_baku')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraint dulu
        Schema::table('submission_details', function (Blueprint $table) {
            $table->dropForeign(['recipe_bahan_baku_id']);
        });

        // Ubah kembali menjadi not null (perhatikan: ini bisa error jika ada data null)
        Schema::table('submission_details', function (Blueprint $table) {
            $table->foreignId('recipe_bahan_baku_id')->nullable(false)->change();
        });

        // Tambahkan kembali foreign key constraint
        Schema::table('submission_details', function (Blueprint $table) {
            $table->foreign('recipe_bahan_baku_id')->references('id')->on('recipe_bahan_baku')->onDelete('cascade');
        });
    }
};
