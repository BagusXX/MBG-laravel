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
            $table->string('kitchen_kode');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->enum('status', ['diajukan', 'diterima', 'ditolak'])->default('diajukan');
            $table->text('keterangan')->nullable();
            $table->date('tanggal')->nullable();
            $table->timestamps();
            $table->index(['kitchen_kode', 'status']);

            $table->foreign('kitchen_kode')->references('kode')->on('kitchens')->OnDelete('cascade');
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
