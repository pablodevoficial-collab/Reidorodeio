<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('affiliate_tiers')) {
            return;
        }

        $tiers = [
            'bronze' => 3.0,
            'silver' => 5.0,
            'gold' => 6.0,
            'diamond' => 7.0,
        ];

        foreach ($tiers as $tier => $fantasyPercent) {
            DB::table('affiliate_tiers')
                ->where('tier', $tier)
                ->update([
                    'x1_commission_percent' => 0,
                    'fantasy_commission_percent' => $fantasyPercent,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('affiliate_tiers')) {
            return;
        }

        $tiers = [
            'bronze' => ['x1' => 20.0, 'fantasy' => 5.0],
            'silver' => ['x1' => 25.0, 'fantasy' => 7.0],
            'gold' => ['x1' => 30.0, 'fantasy' => 8.0],
            'diamond' => ['x1' => 35.0, 'fantasy' => 10.0],
        ];

        foreach ($tiers as $tier => $values) {
            DB::table('affiliate_tiers')
                ->where('tier', $tier)
                ->update([
                    'x1_commission_percent' => $values['x1'],
                    'fantasy_commission_percent' => $values['fantasy'],
                    'updated_at' => now(),
                ]);
        }
    }
};
