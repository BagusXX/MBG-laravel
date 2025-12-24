<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('recipe_bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_id')->constrained('kitchens')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->onDelete('cascade'); // <- perbaikan

            $table->decimal('jumlah', 10, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recipe_bahan_baku');
    }
};
