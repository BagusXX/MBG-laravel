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
        Schema::create('kitchen_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('kitchen_kode');
            $table->foreignId('suppliers_id')->constrained()->cascadeOnDelete();
            $table->foreign('kitchen_kode')->references('kode')->on('kitchens')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_suppliers');
    }
};
