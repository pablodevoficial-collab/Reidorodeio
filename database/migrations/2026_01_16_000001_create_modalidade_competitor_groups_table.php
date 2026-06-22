<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('modalidade_competitor_groups')) {
            Schema::create('modalidade_competitor_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('modalidade_id');
                $table->string('divisao', 60)->nullable();
                $table->string('nome', 120)->nullable();
                $table->unsignedTinyInteger('tamanho')->default(1);
                $table->string('status', 20)->default('ativo');
                $table->timestamps();

                $table->index(['modalidade_id', 'divisao'], 'idx_modalidade_group_divisao');

                if (Schema::hasTable('modalidades')) {
                    $table->foreign('modalidade_id')
                        ->references('id')
                        ->on('modalidades')
                        ->cascadeOnDelete();
                }
            });
        }

        if (!Schema::hasTable('modalidade_competitor_group_members')) {
            Schema::create('modalidade_competitor_group_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('competitor_id');
                $table->timestamps();

                $table->unique(['group_id', 'competitor_id'], 'uq_group_competitor');
                $table->index(['competitor_id'], 'idx_group_competitor');

                if (Schema::hasTable('modalidade_competitor_groups')) {
                    $table->foreign('group_id')
                        ->references('id')
                        ->on('modalidade_competitor_groups')
                        ->cascadeOnDelete();
                }

                if (Schema::hasTable('competitors')) {
                    $table->foreign('competitor_id')
                        ->references('id')
                        ->on('competitors')
                        ->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('modalidade_competitor_group_members');
        Schema::dropIfExists('modalidade_competitor_groups');
    }
};
