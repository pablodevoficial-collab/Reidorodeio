<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('competitor_scoring_logs')) {
            return;
        }

        Schema::create('competitor_scoring_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competitor_id')->constrained('competitors')->cascadeOnDelete();
            $table->foreignId('rodeio_id')->nullable()->constrained('rodeios')->nullOnDelete();
            $table->foreignId('modalidade_id')->nullable()->constrained('modalidades')->nullOnDelete();

            $table->string('action_type', 100);
            $table->string('action_category', 100)->default('outros');
            $table->integer('points');

            $table->integer('total_score_before')->default(0);
            $table->integer('total_score_after')->default(0);

            $table->string('event_phase', 50)->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('scored_at')->nullable();
            $table->string('scored_by', 100)->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Use short, explicit names to avoid MySQL identifier length limits.
            $table->index(['competitor_id', 'scored_at'], 'csl_comp_scored_at_idx');
            $table->index(['rodeio_id', 'modalidade_id'], 'csl_rodeio_modalidade_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_scoring_logs');
    }
};
