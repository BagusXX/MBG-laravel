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
        // Step 1: Tambahkan 'selesai' ke enum (tambah dulu tanpa hapus 'diterima')
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status ENUM('diajukan', 'diproses', 'diterima', 'selesai', 'ditolak') DEFAULT 'diajukan'");
        
        // Step 2: Update data yang sudah ada dari 'diterima' menjadi 'selesai'
        DB::table('submissions')
            ->where('status', 'diterima')
            ->update(['status' => 'selesai']);

        // Step 3: Hapus 'diterima' dari enum (hanya sisakan yang baru)
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status ENUM('diajukan', 'diproses', 'selesai', 'ditolak') DEFAULT 'diajukan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Tambahkan 'diterima' ke enum (tambah dulu tanpa hapus 'selesai')
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status ENUM('diajukan', 'diproses', 'diterima', 'selesai', 'ditolak') DEFAULT 'diajukan'");
        
        // Step 2: Update data yang sudah ada dari 'selesai' menjadi 'diterima'
        DB::table('submissions')
            ->where('status', 'selesai')
            ->update(['status' => 'diterima']);

        // Step 3: Hapus 'selesai' dari enum (kembalikan ke original)
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status ENUM('diajukan', 'diproses', 'diterima', 'ditolak') DEFAULT 'diajukan'");
    }
};
