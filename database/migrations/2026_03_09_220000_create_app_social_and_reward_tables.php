<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_friend_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_user_id');
            $table->unsignedBigInteger('receiver_user_id');
            $table->string('status', 20)->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['sender_user_id', 'receiver_user_id']);
            $table->index(['receiver_user_id', 'status']);
            $table->index(['sender_user_id', 'status']);

            $table->foreign('sender_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('receiver_user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('app_user_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('blocked_user_id');
            $table->timestamps();

            $table->unique(['user_id', 'blocked_user_id']);
            $table->index('blocked_user_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('blocked_user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('app_direct_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_user_id');
            $table->unsignedBigInteger('receiver_user_id');
            $table->text('body');
            $table->json('metadata')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['sender_user_id', 'receiver_user_id', 'created_at'], 'app_dm_sender_receiver_created_idx');
            $table->index(['receiver_user_id', 'sender_user_id', 'read_at'], 'app_dm_receiver_sender_read_idx');

            $table->foreign('sender_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('receiver_user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('app_user_reward_unlocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('code', 80);
            $table->string('title', 120);
            $table->string('description', 255)->nullable();
            $table->string('icon', 24)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'code']);
            $table->index('unlocked_at');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_user_reward_unlocks');
        Schema::dropIfExists('app_direct_messages');
        Schema::dropIfExists('app_user_blocks');
        Schema::dropIfExists('app_friend_requests');
    }
};
