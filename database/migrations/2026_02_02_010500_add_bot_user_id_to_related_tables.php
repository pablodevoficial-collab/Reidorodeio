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
        // X1 Rooms - adicionar bot_user_id para criador e oponente
        Schema::table('x1_rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('bot_criador_id')->nullable()->after('criador_id');
            $table->unsignedBigInteger('bot_oponente_id')->nullable()->after('oponente_id');
            
            $table->foreign('bot_criador_id')->references('id')->on('bot_users')->onDelete('set null');
            $table->foreign('bot_oponente_id')->references('id')->on('bot_users')->onDelete('set null');
            
            $table->index('bot_criador_id');
            $table->index('bot_oponente_id');
        });

        // X1 Participants
        Schema::table('x1_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('bot_user_id')->nullable()->after('user_id');
            
            $table->foreign('bot_user_id')->references('id')->on('bot_users')->onDelete('cascade');
            $table->index('bot_user_id');
        });

        // Fantasy Teams
        Schema::table('fantasy_teams', function (Blueprint $table) {
            $table->unsignedBigInteger('bot_user_id')->nullable()->after('user_id');
            
            $table->foreign('bot_user_id')->references('id')->on('bot_users')->onDelete('cascade');
            $table->index('bot_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fantasy_teams', function (Blueprint $table) {
            $table->dropForeign(['bot_user_id']);
            $table->dropColumn('bot_user_id');
        });

        Schema::table('x1_participants', function (Blueprint $table) {
            $table->dropForeign(['bot_user_id']);
            $table->dropColumn('bot_user_id');
        });

        Schema::table('x1_rooms', function (Blueprint $table) {
            $table->dropForeign(['bot_criador_id']);
            $table->dropForeign(['bot_oponente_id']);
            $table->dropColumn(['bot_criador_id', 'bot_oponente_id']);
        });
    }
};
