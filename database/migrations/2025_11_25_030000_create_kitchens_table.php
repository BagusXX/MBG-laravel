<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void {
Schema::create('kitchens', function (Blueprint $table) {
$table->id();
$table->string('kode')->unique();
$table->string('nama');
$table->string('alamat');
$table->string('kepala_dapur');
$table->string('nomor_kepala_dapur');
$table->timestamps();
});
}


public function down(): void {
Schema::dropIfExists('kitchens');
}
};