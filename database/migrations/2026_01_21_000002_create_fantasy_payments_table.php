<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('fantasy_payments')) {
            Schema::create('fantasy_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('fantasy_league_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('fantasy_team_id')->nullable(); // preenchido após aprovação
                $table->decimal('amount', 10, 2);
                $table->string('provider', 32)->default('mercadopago');
                $table->string('external_reference', 128)->nullable();
                $table->string('provider_payment_id', 128)->nullable();
                $table->string('provider_preference_id', 128)->nullable();
                $table->string('status', 24)->default('pending'); // pending, approved, cancelled, expired
                $table->json('payload')->nullable(); // stores competitor_ids, team_name, pix data
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('expires_at')->nullable(); // 5 min after creation
                $table->timestamps();

                $table->index('fantasy_league_id');
                $table->index('user_id');
                $table->index('status');
                $table->index('provider_preference_id');
                $table->index('external_reference');

                $table->foreign('fantasy_league_id')->references('id')->on('fantasy_leagues')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fantasy_payments');
    }
};
