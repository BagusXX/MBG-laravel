<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void {
Schema::table('users', function (Blueprint $table) {
$table->string('role')->nullable();
$table->unsignedBigInteger('kitchen_id')->nullable();


$table->foreign('kitchen_id')->references('id')->on('kitchens')->onDelete('set null');
});
}


public function down(): void {
Schema::table('users', function (Blueprint $table) {
$table->dropForeign(['kitchen_id']);
$table->dropColumn(['role','kitchen_id']);
});
}
};