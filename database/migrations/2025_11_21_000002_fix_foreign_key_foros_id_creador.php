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
        Schema::table('foros', function (Blueprint $table) {
            // Drop the incorrect foreign key
            $table->dropForeign(['ID_Creador']);
            // Add the correct foreign key referencing 'id' on usuarios_campus_markets
            $table->foreign('ID_Creador')->references('id')->on('usuarios_campus_markets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foros', function (Blueprint $table) {
            // Drop the correct foreign key
            $table->dropForeign(['ID_Creador']);
            // Add back the incorrect foreign key (for rollback)
            $table->foreign('ID_Creador')->references('ID_Usuario')->on('usuarios_campus_markets');
        });
    }
};
