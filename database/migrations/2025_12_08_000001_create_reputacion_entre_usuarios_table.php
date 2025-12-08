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
        // Solo crear si no existe
        if (!Schema::hasTable('reputacion_entre_usuarios')) {
            Schema::create('reputacion_entre_usuarios', function (Blueprint $table) {
                $table->bigIncrements('ID_Reputacion');
                $table->unsignedBigInteger('ID_Usuario_Calificador');
                $table->unsignedBigInteger('ID_Usuario_Calificado');
                $table->unsignedTinyInteger('Puntuacion');
                $table->text('Comentario')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->foreign('ID_Usuario_Calificador')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('ID_Usuario_Calificado')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reputacion_entre_usuarios');
    }
};
