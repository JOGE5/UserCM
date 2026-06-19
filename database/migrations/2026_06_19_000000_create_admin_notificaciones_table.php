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
        Schema::create('admin_notificaciones', function (Blueprint $table) {
            $table->id('ID_Notificacion');
            $table->string('tipo_envio', 50); // Usuario especifico, Por rol, A todos
            $table->string('Destinatario_Notificacion'); // Email o descripcion (Ej. Rol: Moderador)
            $table->unsignedBigInteger('ID_Usuario')->nullable(); // Si fue a un usuario
            $table->unsignedBigInteger('Cod_Rol')->nullable(); // Si fue a un rol
            $table->string('Titulo_Notificacion', 150);
            $table->text('Mensaje_Notificacion');
            $table->string('imgen')->nullable();
            $table->string('Estado_Notificacion', 20)->default('Pendiente'); // Enviado, Error
            $table->timestamp('Fecha_Envio')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notificaciones');
    }
};
