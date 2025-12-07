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
        Schema::create('reportsPubli', function (Blueprint $table) {
            $table->bigIncrements('id');

           
            $table->morphs('reportable');

            $table->unsignedBigInteger('reporter_id')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('reporter_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportsPubli');  
    }
};
