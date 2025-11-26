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
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');      // Primary Key
            $table->string('name');           // Nama bahan
            $table->string('category');       // Kategori (bumbu, bahan, sayur, dll)
            $table->integer('stock');         // Stok
            $table->string('unit');           // Satuan (kg, liter, pcs)
            $table->integer('price')->nullable(); // Harga (boleh null)
            $table->timestamps();             // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
