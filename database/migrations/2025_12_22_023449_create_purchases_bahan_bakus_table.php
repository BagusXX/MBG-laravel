<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('purchase_bahan_bakus')) {
            Schema::create('purchase_bahan_bakus', function (Blueprint $table) {
                $table->id()->autoIncrement(true);
                $table->string('kode')->unique();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');

                $table->decimal('total', 15, 2)->default(0);
                $table->date('tanggal');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
