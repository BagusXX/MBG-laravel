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
        $table->foreignId('kitchen_id')->constrained()->onDelete('cascade');
        $table->foreignId('menu_id')->constrained()->onDelete('cascade');
        $table->integer('porsi');
        $table->timestamps();
    });
}


    
    public function down(): void
    {
        Schema::dropIfExists('submission');
    }
};
