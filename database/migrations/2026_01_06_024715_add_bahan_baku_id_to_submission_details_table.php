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
        Schema::table('submission_details', function (Blueprint $table) {
            $table->foreignId('bahan_baku_id')->nullable()->after('recipe_bahan_baku_id')->constrained('bahan_baku')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_details', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_id']);
            $table->dropColumn('bahan_baku_id');
        });
    }
};
