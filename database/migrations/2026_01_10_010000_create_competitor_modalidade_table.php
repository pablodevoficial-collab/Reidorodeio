<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('competitor_modalidade')) {
            return;
        }

        $hasCompetitors = Schema::hasTable('competitors');
        $hasModalidades = Schema::hasTable('modalidades');

        Schema::create('competitor_modalidade', function (Blueprint $table) use ($hasCompetitors, $hasModalidades) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('competitor_id');
            $table->unsignedBigInteger('modalidade_id');

            $table->string('status')->default('inscrito');
            $table->unsignedInteger('numero_participacao')->nullable();
            $table->decimal('multiplicador_atual', 10, 2)->nullable();
            $table->boolean('disponivel_participacao')->default(true);
            $table->json('dados_especificos')->nullable();
            $table->text('observacoes')->nullable();
            $table->unsignedInteger('posicao_final')->nullable();

            $table->timestamps();

            $table->unique(['competitor_id', 'modalidade_id'], 'uq_competitor_modalidade');
            $table->index(['modalidade_id', 'status'], 'idx_modalidade_status');
            $table->index(['competitor_id', 'status'], 'idx_competitor_status');

            if ($hasCompetitors) {
                $table->foreign('competitor_id')
                    ->references('id')
                    ->on('competitors')
                    ->cascadeOnDelete();
            }

            if ($hasModalidades) {
                $table->foreign('modalidade_id')
                    ->references('id')
                    ->on('modalidades')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_modalidade');
    }
};
