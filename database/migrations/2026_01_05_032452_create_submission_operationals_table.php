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

            // SELF-REFERENCE (parent-child)
            $table->foreignId('parent_id')->nullable()->constrained('submission_operationals')->nullOnDelete();
            $table->string('kitchen_kode');

             // supplier hanya untuk CHILD (approval)
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            // tipe record
            $table->enum('tipe', ['pengajuan', 'approval']);

            $table->decimal('total_harga', 15, 2)->default(0);
            $table->enum('status', [ 
                'diajukan',
                'diproses',
                'approved',
                'ditolak',
                'selesai'])->default('diajukan');
            
            $table->text('keterangan')->nullable();
            $table->date('tanggal')->nullable();
            $table->timestamps();

            $table->index(['kitchen_kode', 'status']);
            $table->index(['parent_id', 'supplier_id']);


            $table->foreign('kitchen_kode')
                ->references('kode')
                ->on('kitchens')
                ->cascadeOnDelete();
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
