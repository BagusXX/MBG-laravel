<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
{
    Schema::create('submission', function (Blueprint $table) {
        $table->id();
        $table->string('kode');
        $table->date('tanggal');
        $table->foreignId('kitchen_id')->constrained('kitchens')->onDelete('cascade');
        $table->foreignId('recipe_bahan_baku_id')->constrained('recipe_bahan_baku')->onDelete('cascade');
        $table->double('total_harga');
        $table->integer('porsi');
        $table->timestamps();
        $table->softDeletes();
    });
}


    
    public function down(): void
    {
        Schema::dropIfExists('submission');
    }
};
