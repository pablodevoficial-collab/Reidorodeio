<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('modalidade_odds_settings')) {
            Schema::create('modalidade_odds_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('modalidade_id')->unique();
                $table->boolean('is_enabled')->default(true);
                $table->decimal('bankroll_gate_amount', 12, 2)->default(500.00);
                $table->unsignedInteger('low_bet_threshold')->default(3);
                $table->unsignedInteger('very_low_bet_threshold')->default(1);
                $table->decimal('low_bet_boost', 6, 3)->default(0.120);
                $table->decimal('very_low_bet_boost', 6, 3)->default(0.220);
                $table->decimal('max_free_odd', 5, 2)->default(2.20);
                $table->decimal('max_premium_odd', 5, 2)->default(2.30);
                $table->decimal('min_house_margin_percent', 5, 2)->default(30.00);
                $table->timestamps();

                $table->foreign('modalidade_id', 'mod_odds_settings_modalidade_fk')
                    ->references('id')
                    ->on('modalidades')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('modalidade_odds_settings');
    }
};
