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
    Schema::create('recipe_items', function (Blueprint $table) {
        $table->bigIncrements('id');
        
        $table->unsignedBigInteger('recipe_id');
        $table->unsignedBigInteger('item_id');

        $table->integer('quantity'); // jumlah bahan yg dipakai
        $table->string('unit');      // satuan bahan (siung, sdt, sdm, gram, pcs)

        $table->timestamps();

        // Foreign key
        $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');
        $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::dropIfExists('recipe_items');
}

};
