<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_community_posts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('message');
            $table->string('subtype', 50)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('emoji', 24)->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->json('metadata')->nullable();
            $table->string('dedupe_key')->nullable()->unique();
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_community_posts');
    }
};
