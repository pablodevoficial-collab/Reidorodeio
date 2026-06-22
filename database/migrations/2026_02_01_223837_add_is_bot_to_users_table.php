<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_bot')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_bot')->default(false)->after('email');
            });
        }
        
        if (!Schema::hasColumn('x1_rooms', 'is_bot_room')) {
            Schema::table('x1_rooms', function (Blueprint $table) {
                $table->boolean('is_bot_room')->default(false)->after('status');
            });
        }
        
        if (!Schema::hasColumn('fantasy_leagues', 'is_bot_league')) {
            Schema::table('fantasy_leagues', function (Blueprint $table) {
                $table->boolean('is_bot_league')->default(false)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_bot')) {
                $table->dropColumn('is_bot');
            }
        });
        
        Schema::table('x1_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('x1_rooms', 'is_bot_room')) {
                $table->dropColumn('is_bot_room');
            }
        });
        
        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (Schema::hasColumn('fantasy_leagues', 'is_bot_league')) {
                $table->dropColumn('is_bot_league');
            }
        });
    }
};
