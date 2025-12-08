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
        if (!Schema::hasTable('reputacion_estado_usuarios')) {
            Schema::create('reputacion_estado_usuarios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->enum('estado_actual', ['Malo', 'Regular', 'Bueno', 'Excelente'])->default('Regular');
                $table->decimal('p_malo', 8, 6)->default(0.25);
                $table->decimal('p_regular', 8, 6)->default(0.25);
                $table->decimal('p_bueno', 8, 6)->default(0.25);
                $table->decimal('p_excelente', 8, 6)->default(0.25);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reputacion_estado_usuarios');
    }
};
