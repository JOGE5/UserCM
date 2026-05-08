<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->string('ubicacion')->nullable()->after('Vistas_Publicacion');
            $table->enum('condicion_producto', ['nuevo', 'usado'])->default('usado')->after('ubicacion');
        });
    }

    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->dropColumn(['ubicacion', 'condicion_producto']);
        });
    }
};
