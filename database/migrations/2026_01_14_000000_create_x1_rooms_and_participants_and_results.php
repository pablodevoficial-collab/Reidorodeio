<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('x1_rooms')) {
            Schema::create('x1_rooms', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('host_user_id')->nullable();
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('modalidade_id')->nullable();
                $table->decimal('valor_entrada', 10, 2)->nullable()->default(0);
                $table->enum('status', ['open','in_progress','closed','cancelled'])->default('open');
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->timestamp('closed_at')->nullable();
            });
        }

        if (!Schema::hasTable('x1_participants')) {
            Schema::create('x1_participants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('x1_room_id');
                $table->unsignedBigInteger('user_id');
                $table->tinyInteger('slot')->default(0);
                $table->json('result')->nullable();
                $table->timestamps();

                $table->foreign('x1_room_id')->references('id')->on('x1_rooms')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('x1_results')) {
            Schema::create('x1_results', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('x1_room_id');
                $table->unsignedBigInteger('winner_user_id')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->foreign('x1_room_id')->references('id')->on('x1_rooms')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('x1_results');
        Schema::dropIfExists('x1_participants');
        Schema::dropIfExists('x1_rooms');
    }
};
