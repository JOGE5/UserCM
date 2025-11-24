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

            // Relaciones
            $table->foreignId('Cod_Categoria')
                  ->constrained('categorias_articulos', 'Cod_Categoria');

            $table->foreignId('ID_Vendedor')
                  ->constrained('usuarios_campus_markets', 'id')
                  ->onDelete('cascade');

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
