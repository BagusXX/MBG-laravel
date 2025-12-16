<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipe_bahan_baku', function (Blueprint $table) {
            $table->integer('porsi')->nullable()->after('satuan');
        });
    }

    public function down(): void
    {
        Schema::table('recipe_bahan_baku', function (Blueprint $table) {
            $table->dropColumn('porsi');
        });
    }
};
