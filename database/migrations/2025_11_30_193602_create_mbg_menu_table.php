<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void {
Schema::create('mbg_menus', function (Blueprint $table) {
$table->id();
$table->unsignedBigInteger('mbg_menu_id'); 
$table->unsignedBigInteger('recipe_id');
$table->integer('porsi');
$table->timestamps();


$table->foreign('recipe_id')->references('id')->on('menus')->onDelete('cascade');
});
}


public function down(): void {
Schema::dropIfExists('mbg_menus');
}
};