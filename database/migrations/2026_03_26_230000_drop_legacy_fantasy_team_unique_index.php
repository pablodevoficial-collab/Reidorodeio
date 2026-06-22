<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fantasy_teams')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $indexExists = collect(DB::select(
                'SHOW INDEX FROM fantasy_teams WHERE Key_name = ?',
                ['fantasy_teams_unique_user_per_league']
            ))->isNotEmpty();

            if ($indexExists) {
                DB::statement('ALTER TABLE fantasy_teams DROP INDEX fantasy_teams_unique_user_per_league');
            }

            return;
        }

        try {
            Schema::table('fantasy_teams', function ($table) {
                $table->dropUnique('fantasy_teams_unique_user_per_league');
            });
        } catch (\Throwable $exception) {
            // Ignora quando a constraint já não existe neste ambiente.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('fantasy_teams')) {
            return;
        }

        try {
            Schema::table('fantasy_teams', function ($table) {
                $table->unique(['fantasy_league_id', 'user_id'], 'fantasy_teams_unique_user_per_league');
            });
        } catch (\Throwable $exception) {
            // No-op.
        }
    }
};
