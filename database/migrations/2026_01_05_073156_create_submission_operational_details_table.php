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
        Schema::create('submission_operational_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operational_submission_id')->constrained('submission_operationals')->cascadeOnDelete();
            $table->foreignId('operational_id')->constrained('operationals')->cascadeOnDelete();
            // supplier di detail OPTIONAL
            // (jika mau fleksibel per item)
            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->nullOnDelete();

            $table->decimal('qty', 10, 2);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('harga_dapur', 15, 2)->default(0);
            $table->decimal('harga_mitra', 15, 2)->nullable();
            $table->decimal('subtotal_dapur', 15, 2)->default(0);
            $table->decimal('subtotal_mitra', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('operational_id');
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
