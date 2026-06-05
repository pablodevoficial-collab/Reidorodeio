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
        // Adicionar divisao à tabela x1_rooms (se existir)
        if (Schema::hasTable('x1_rooms') && !Schema::hasColumn('x1_rooms', 'divisao')) {
            Schema::table('x1_rooms', function (Blueprint $table) {
                $table->string('divisao')->nullable()->after('modalidade_id');
            });
        }
        
        // Adicionar divisao à tabela x1_room_instances (se existir)
        if (Schema::hasTable('x1_room_instances') && !Schema::hasColumn('x1_room_instances', 'divisao')) {
            Schema::table('x1_room_instances', function (Blueprint $table) {
                $table->string('divisao')->nullable()->after('modalidade_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('x1_rooms') && Schema::hasColumn('x1_rooms', 'divisao')) {
            Schema::table('x1_rooms', function (Blueprint $table) {
                $table->dropColumn('divisao');
            });
        }
        
        if (Schema::hasTable('x1_room_instances') && Schema::hasColumn('x1_room_instances', 'divisao')) {
            Schema::table('x1_room_instances', function (Blueprint $table) {
                $table->dropColumn('divisao');
            });
        }
    }
};
