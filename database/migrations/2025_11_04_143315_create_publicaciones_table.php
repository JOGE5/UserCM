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
        Schema::create('publicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('Titulo_Publicacion', 200);
            $table->text('Descripcion_Publicacion');
            $table->boolean('Estado_Publicacion')->default(true);
            $table->decimal('Precio_Publicacion', 10, 2);
            $table->string('Imagen_Publicacion')->nullable();
            $table->unsignedBigInteger('Cod_Categoria');
            $table->unsignedBigInteger('ID_Vendedor');

            // Relaciones
            $table->foreign('Cod_Categoria')
                  ->references('Cod_Categoria')
                  ->on('categorias_articulos');

            $table->foreign('ID_Vendedor')
                  ->references('ID_Usuario')
                  ->on('usuarios_campus_markets');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publicaciones');
    }
};
