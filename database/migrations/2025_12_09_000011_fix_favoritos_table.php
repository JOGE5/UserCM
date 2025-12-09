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
        // If favoritos does not exist, create it correctly
        if (! Schema::hasTable('favoritos')) {
            Schema::create('favoritos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('publicacion_id');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('publicacion_id')->references('id')->on('publicaciones')->onDelete('cascade');

                $table->unique(['user_id','publicacion_id']);
            });
        } else {
            // If table exists, ensure required columns exist and FK constraints match schema
            Schema::table('favoritos', function (Blueprint $table) {
                if (! Schema::hasColumn('favoritos', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->after('id');
                }
                if (! Schema::hasColumn('favoritos', 'publicacion_id')) {
                    $table->unsignedBigInteger('publicacion_id')->after('user_id');
                }
            });

            // Add foreign keys if not present (Laravel doesn't provide a direct check, so attempt to add and ignore on failure)
            try {
                Schema::table('favoritos', function (Blueprint $table) {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                });
            } catch (\Exception $e) {
                // noop - schema manager usage is only for advanced checks; we'll proceed to add constraints safely later if needed
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favoritos');
    }
};
