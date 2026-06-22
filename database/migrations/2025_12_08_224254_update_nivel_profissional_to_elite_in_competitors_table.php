<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Troca: profissional -> elite
     */
    public function up(): void
    {
        // Atualizar os dados existentes
        DB::table('competitors')->where('nivel', 'profissional')->update(['nivel' => 'elite']);

        // Alterar o enum da coluna
        DB::statement("ALTER TABLE competitors MODIFY COLUMN nivel ENUM('favorito', 'elite', 'legado', 'presilha') NOT NULL DEFAULT 'presilha'");
    }

    /**
     * Reverse the migrations.
     * Reverte: elite -> profissional
     */
    public function down(): void
    {
        // Reverter os dados
        DB::table('competitors')->where('nivel', 'elite')->update(['nivel' => 'profissional']);

        // Reverter o enum da coluna
        DB::statement("ALTER TABLE competitors MODIFY COLUMN nivel ENUM('favorito', 'profissional', 'legado', 'presilha') NOT NULL DEFAULT 'presilha'");
    }
};
