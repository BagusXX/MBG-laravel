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
        $table->unsignedBigInteger('kitchen_id')->after('satuan');

        // kalau ingin relasi foreign key ↓↓↓
        $table->foreign('kitchen_id')->references('id')->on('kitchens')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('bahan_baku', function (Blueprint $table) {
        $table->dropForeign(['kitchen_id']);
        $table->dropColumn('kitchen_id');
    });
}

};
