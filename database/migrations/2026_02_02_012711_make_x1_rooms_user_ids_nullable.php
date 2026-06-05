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
        Schema::table('x1_rooms', function (Blueprint $table) {
            // Tornar criador_id e oponente_id nullable para permitir salas de bots
            $table->unsignedBigInteger('criador_id')->nullable()->change();
            $table->unsignedBigInteger('oponente_id')->nullable()->change();
            
            // Tornar host_user_id e opponent_user_id nullable também
            if (Schema::hasColumn('x1_rooms', 'host_user_id')) {
                $table->unsignedBigInteger('host_user_id')->nullable()->change();
            }
            if (Schema::hasColumn('x1_rooms', 'opponent_user_id')) {
                $table->unsignedBigInteger('opponent_user_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Forward-only migration
    }
};
