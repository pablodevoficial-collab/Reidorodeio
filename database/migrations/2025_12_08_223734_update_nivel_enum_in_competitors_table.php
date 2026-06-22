<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Troca: estrela -> favorito, favorito -> profissional
     */
    public function up(): void
    {
        // Primeiro, atualizar os dados existentes (na ordem correta para evitar conflitos)
        // 1. favorito -> profissional (primeiro para não conflitar)
        DB::table('competitors')->where('nivel', 'favorito')->update(['nivel' => 'profissional_temp']);
        // 2. estrela -> favorito
        DB::table('competitors')->where('nivel', 'estrela')->update(['nivel' => 'favorito']);
        // 3. profissional_temp -> profissional
        DB::table('competitors')->where('nivel', 'profissional_temp')->update(['nivel' => 'profissional']);

        // Alterar o enum da coluna
        DB::statement("ALTER TABLE competitors MODIFY COLUMN nivel ENUM('favorito', 'profissional', 'legado', 'presilha') NOT NULL DEFAULT 'presilha'");
    }

    /**
     * Reverse the migrations.
     * Reverte: favorito -> estrela, profissional -> favorito
     */
    public function down(): void
    {
        // Reverter os dados (na ordem correta para evitar conflitos)
        // 1. favorito -> estrela (primeiro para não conflitar)
        DB::table('competitors')->where('nivel', 'favorito')->update(['nivel' => 'estrela_temp']);
        // 2. profissional -> favorito
        DB::table('competitors')->where('nivel', 'profissional')->update(['nivel' => 'favorito']);
        // 3. estrela_temp -> estrela
        DB::table('competitors')->where('nivel', 'estrela_temp')->update(['nivel' => 'estrela']);

        // Reverter o enum da coluna
        DB::statement("ALTER TABLE competitors MODIFY COLUMN nivel ENUM('estrela', 'favorito', 'legado', 'presilha') NOT NULL DEFAULT 'presilha'");
    }
};
