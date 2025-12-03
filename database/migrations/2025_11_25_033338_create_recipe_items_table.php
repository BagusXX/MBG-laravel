<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void {
Schema::create('recipe_items', function (Blueprint $table) {
$table->id();
$table->unsignedBigInteger('recipe_id');
$table->unsignedBigInteger('item_id');
$table->integer('quantity');
$table->string('unit');
$table->timestamps();


$table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');
$table->foreign('item_id')->references('id')->on('bahan_baku')->onDelete('cascade');
});
}


public function down(): void {
Schema::dropIfExists('recipe_items');
}
};