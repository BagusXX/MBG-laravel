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
            $table->foreignId('bahan_baku_id')->nullable()->constrained('bahan_baku')->onDelete('cascade');
           
            $table->decimal('qty_digunakan',15,4);// Total berat (takaran x porsi)
            $table->decimal('harga_satuan_saat_itu',15,2); // Harga per gram saat submit
            $table->decimal('harga_dapur', 15, 2)->nullable();
            $table->decimal('harga_mitra', 15, 2)->nullable();
            $table->decimal('subtotal_harga',15,2);
             // <--- HARGA PER BAHAN DIKALI PORSI MASUK SINI

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
