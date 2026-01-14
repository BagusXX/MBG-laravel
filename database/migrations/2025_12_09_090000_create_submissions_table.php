<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->date('tanggal');
            $table->foreignId('kitchen_id')->constrained('kitchens')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->integer('porsi');
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->enum('tipe', ['pengajuan', 'disetujui'])->default('pengajuan');
            $table->enum('status', [
                'diajukan',
                'diproses',
                'diterima',
                'ditolak',
                'selesai',
            ])->default('diajukan');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('submissions')
                ->nullOnDelete();
            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }



    public function down(): void
    {
        Schema::dropIfExists('submission');
    }
};
