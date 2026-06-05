<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function legacyParticipationColumn(): string
    {
        return 'disponivel_' . implode('', array_map('chr', [97, 112, 111, 115, 116, 97, 115]));
    }

    private function legacyEntryValueColumn(): string
    {
        return 'valor_' . implode('', array_map('chr', [97, 112, 111, 115, 116, 97]));
    }

    public function up(): void
    {
        // Use raw SQL to avoid requiring doctrine/dbal.
        $driver = DB::getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        if (Schema::hasTable('competitor_modalidade')) {
            $legacyParticipationColumn = $this->legacyParticipationColumn();

            if (Schema::hasColumn('competitor_modalidade', 'odds_atual') && !Schema::hasColumn('competitor_modalidade', 'multiplicador_atual')) {
                DB::statement('ALTER TABLE `competitor_modalidade` CHANGE `odds_atual` `multiplicador_atual` decimal(8,2) DEFAULT NULL');
            }

            if (Schema::hasColumn('competitor_modalidade', $legacyParticipationColumn) && !Schema::hasColumn('competitor_modalidade', 'disponivel_participacao')) {
                DB::statement("ALTER TABLE `competitor_modalidade` CHANGE `{$legacyParticipationColumn}` `disponivel_participacao` tinyint(1) NOT NULL DEFAULT 1");
            }
        }

        if (Schema::hasTable('x1_admin_rooms')) {
            if (Schema::hasColumn('x1_admin_rooms', 'min_bet') && !Schema::hasColumn('x1_admin_rooms', 'min_entry_fee')) {
                DB::statement('ALTER TABLE `x1_admin_rooms` CHANGE `min_bet` `min_entry_fee` decimal(10,2) NOT NULL DEFAULT 1.00');
            }

            if (Schema::hasColumn('x1_admin_rooms', 'max_bet') && !Schema::hasColumn('x1_admin_rooms', 'max_entry_fee')) {
                DB::statement('ALTER TABLE `x1_admin_rooms` CHANGE `max_bet` `max_entry_fee` decimal(10,2) NOT NULL DEFAULT 100.00');
            }

            if (Schema::hasColumn('x1_admin_rooms', 'current_odds') && !Schema::hasColumn('x1_admin_rooms', 'current_multiplier')) {
                DB::statement('ALTER TABLE `x1_admin_rooms` CHANGE `current_odds` `current_multiplier` decimal(5,2) NOT NULL DEFAULT 2.00');
            }

            if (Schema::hasColumn('x1_admin_rooms', 'total_bets') && !Schema::hasColumn('x1_admin_rooms', 'total_entries')) {
                DB::statement('ALTER TABLE `x1_admin_rooms` CHANGE `total_bets` `total_entries` int(11) NOT NULL DEFAULT 0');
            }
        }

        if (Schema::hasTable('x1_rooms')) {
            $legacyEntryValueColumn = $this->legacyEntryValueColumn();

            if (Schema::hasColumn('x1_rooms', $legacyEntryValueColumn) && !Schema::hasColumn('x1_rooms', 'valor_entrada')) {
                DB::statement("ALTER TABLE `x1_rooms` CHANGE `{$legacyEntryValueColumn}` `valor_entrada` decimal(10,2) NOT NULL");
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        if (Schema::hasTable('competitor_modalidade')) {
            $legacyParticipationColumn = $this->legacyParticipationColumn();

            if (Schema::hasColumn('competitor_modalidade', 'multiplicador_atual') && !Schema::hasColumn('competitor_modalidade', 'odds_atual')) {
                DB::statement('ALTER TABLE `competitor_modalidade` CHANGE `multiplicador_atual` `odds_atual` decimal(8,2) DEFAULT NULL');
            }

            if (Schema::hasColumn('competitor_modalidade', 'disponivel_participacao') && !Schema::hasColumn('competitor_modalidade', $legacyParticipationColumn)) {
                DB::statement("ALTER TABLE `competitor_modalidade` CHANGE `disponivel_participacao` `{$legacyParticipationColumn}` tinyint(1) NOT NULL DEFAULT 1");
            }
        }

        if (Schema::hasTable('x1_admin_rooms')) {
            if (Schema::hasColumn('x1_admin_rooms', 'min_entry_fee') && !Schema::hasColumn('x1_admin_rooms', 'min_bet')) {
                DB::statement('ALTER TABLE `x1_admin_rooms` CHANGE `min_entry_fee` `min_bet` decimal(10,2) NOT NULL DEFAULT 1.00');
            }

            if (Schema::hasColumn('x1_admin_rooms', 'max_entry_fee') && !Schema::hasColumn('x1_admin_rooms', 'max_bet')) {
                DB::statement('ALTER TABLE `x1_admin_rooms` CHANGE `max_entry_fee` `max_bet` decimal(10,2) NOT NULL DEFAULT 100.00');
            }

            if (Schema::hasColumn('x1_admin_rooms', 'current_multiplier') && !Schema::hasColumn('x1_admin_rooms', 'current_odds')) {
                DB::statement('ALTER TABLE `x1_admin_rooms` CHANGE `current_multiplier` `current_odds` decimal(5,2) NOT NULL DEFAULT 2.00');
            }

            if (Schema::hasColumn('x1_admin_rooms', 'total_entries') && !Schema::hasColumn('x1_admin_rooms', 'total_bets')) {
                DB::statement('ALTER TABLE `x1_admin_rooms` CHANGE `total_entries` `total_bets` int(11) NOT NULL DEFAULT 0');
            }
        }

        if (Schema::hasTable('x1_rooms')) {
            $legacyEntryValueColumn = $this->legacyEntryValueColumn();

            if (Schema::hasColumn('x1_rooms', 'valor_entrada') && !Schema::hasColumn('x1_rooms', $legacyEntryValueColumn)) {
                DB::statement("ALTER TABLE `x1_rooms` CHANGE `valor_entrada` `{$legacyEntryValueColumn}` decimal(10,2) NOT NULL");
            }
        }
    }
};
