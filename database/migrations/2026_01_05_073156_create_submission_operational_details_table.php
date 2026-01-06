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
        Schema::create('submission_operational_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operational_submission_id')->constrained('submission_operationals')->cascadeOnDelete();
            $table->foreignId('operational_id')->constrained('operationals')->cascadeOnDelete();
            $table->decimal('qty', 10, 2);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('barang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_operational_details');
    }
};
