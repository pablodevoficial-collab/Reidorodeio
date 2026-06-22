<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fantasy_leagues')) {
            return;
        }

        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (!Schema::hasColumn('fantasy_leagues', 'rodeio_id')) {
                $table->unsignedBigInteger('rodeio_id')->nullable()->after('season_id');
                $table->index(['rodeio_id'], 'fantasy_leagues_rodeio_idx');
            }

            if (!Schema::hasColumn('fantasy_leagues', 'modalidade_id')) {
                $table->unsignedBigInteger('modalidade_id')->nullable()->after('rodeio_id');
                $table->index(['modalidade_id'], 'fantasy_leagues_modalidade_idx');
            }

            if (!Schema::hasColumn('fantasy_leagues', 'divisao')) {
                $table->string('divisao', 60)->default('')->after('modalidade_id');
                $table->index(['rodeio_id', 'modalidade_id', 'divisao'], 'fantasy_leagues_ctx_idx');
            }
        });

        // FKs (em bloco separado para evitar problemas em SQLite/ambientes sem rodeios/modalidades)
        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (Schema::hasTable('rodeios') && Schema::hasColumn('fantasy_leagues', 'rodeio_id')) {
                try {
                    $table->foreign('rodeio_id', 'fantasy_leagues_rodeio_id_fk')->references('id')->on('rodeios')->nullOnDelete();
                } catch (Throwable $e) {
                    // ignore
                }
            }

            if (Schema::hasTable('modalidades') && Schema::hasColumn('fantasy_leagues', 'modalidade_id')) {
                try {
                    $table->foreign('modalidade_id', 'fantasy_leagues_modalidade_id_fk')->references('id')->on('modalidades')->nullOnDelete();
                } catch (Throwable $e) {
                    // ignore
                }
            }
        });
    }

    public function down(): void
    {
        // Forward-only
    }
};
