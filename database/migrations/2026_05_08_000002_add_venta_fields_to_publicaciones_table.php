<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->timestamp('vendido_at')->nullable()->after('condicion_producto');
            $table->unsignedBigInteger('comprador_id')->nullable()->after('vendido_at');
            $table->foreign('comprador_id')
                  ->references('id')
                  ->on('usuarios_campus_markets')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->dropForeign(['comprador_id']);
            $table->dropColumn(['vendido_at', 'comprador_id']);
        });
    }
};
