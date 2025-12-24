<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('submission_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->foreignId('recipe_bahan_baku_id')->constrained('recipe_bahan_baku')->onDelete('cascade');

            $table->double('qty_digunakan');// Total berat (takaran x porsi)
            $table->double('harga_satuan_saat_itu'); // Harga per gram saat submit
            $table->double('subtotal_harga'); // <--- HARGA PER BAHAN DIKALI PORSI MASUK SINI

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_details');
    }
};
