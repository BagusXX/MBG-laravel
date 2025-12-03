<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('bahan_baku', function (Blueprint $table) {
        $table->string('kode', 10)->after('id')->unique()->nullable();
        $table->unsignedBigInteger('kitchen_id')->after('kode')->nullable();

        // Jika ingin relasi
        $table->foreign('kitchen_id')->references('id')->on('kitchens')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('bahan_baku', function (Blueprint $table) {
        $table->dropForeign(['kitchen_id']);
        $table->dropColumn(['kode', 'kitchen_id']);
    });
}

};
