<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitor_follow_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competitor_id')->constrained('competitors')->cascadeOnDelete();
            $table->string('event_type', 50);
            $table->string('title');
            $table->text('message');
            $table->string('cta_label', 100)->nullable();
            $table->string('cta_url')->nullable();
            $table->string('source_key')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('rodeio_id')->nullable()->constrained('rodeios')->nullOnDelete();
            $table->foreignId('modalidade_id')->nullable()->constrained('modalidades')->nullOnDelete();
            $table->foreignId('fantasy_league_id')->nullable()->constrained('fantasy_leagues')->nullOnDelete();
            $table->foreignId('scoring_log_id')->nullable()->constrained('competitor_scoring_logs')->nullOnDelete();
            $table->timestamps();

            $table->unique(['competitor_id', 'source_key']);
            $table->index(['competitor_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_follow_events');
    }
};
