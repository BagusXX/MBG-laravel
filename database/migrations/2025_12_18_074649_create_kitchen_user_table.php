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
        Schema::create('kitchen_user', function (Blueprint $table) {
            $table->id();
            $table->string('kitchen_kode');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreign('kitchen_kode')->references('kode')->on('kitchens')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_user');
    }
};
