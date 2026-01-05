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
        Schema::create('submission_operationals', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->foreignId('kitchen_id')->constrained('kitchens')->cascadeOnDelete();
            $table->foreignId('operasional_id')->constrained('operationals')->cascadeOnDelete();
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->enum('status',['diajukan','diterima','ditolak']);
            $table->timestamps();
            $table->index(['kitchen_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_operationals');
    }
};
