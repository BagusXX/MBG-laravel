<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void {
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->string('kode');
            $table->string('nama');
            $table->double('harga');
            $table->foreignId('satuan_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('kitchen_id')->constrained('kitchens')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }


public function down(): void {
    Schema::dropIfExists('bahan_baku');
}
};