<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fantasy_leagues')) {
            Schema::create('fantasy_leagues', function (Blueprint $table) {
                $table->id();

                $table->string('name');
                $table->string('category', 50)->default('fantasy');
                $table->decimal('price', 10, 2)->default(0);
                $table->boolean('is_premium')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('max_users')->nullable();
                $table->unsignedBigInteger('season_id')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index('category');
                $table->index('is_premium');
                $table->index('is_active');
                $table->index('season_id');
            });

            return;
        }

        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (!Schema::hasColumn('fantasy_leagues', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'category')) {
                $table->string('category', 50)->default('fantasy')->after('name');
                $table->index('category');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('category');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'is_premium')) {
                $table->boolean('is_premium')->default(false)->after('price');
                $table->index('is_premium');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_premium');
                $table->index('is_active');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'max_users')) {
                $table->unsignedInteger('max_users')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'season_id')) {
                $table->unsignedBigInteger('season_id')->nullable()->after('max_users');
                $table->index('season_id');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        // Forward-only migration by project convention.
    }
};
