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
        Schema::create('api_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id'); // Llave foránea
            $table->json('close_friends')->nullable();
            $table->json('followers')->nullable();
            $table->json('following')->nullable();
            $table->json('hide_story_from')->nullable();
            $table->json('pending_follow_requests')->nullable();
            $table->json('recent_follow_requests')->nullable();
            $table->json('recently_unfollowed_accounts')->nullable();
            $table->json('removed_suggestions')->nullable();
            $table->timestamps();

            // Definir la restricción de clave foránea
            $table->foreign('usuario_id')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_data');
    }
};
