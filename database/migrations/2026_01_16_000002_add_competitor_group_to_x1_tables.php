<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('x1_rooms')) {
            Schema::table('x1_rooms', function (Blueprint $table) {
                if (!Schema::hasColumn('x1_rooms', 'competitor_group_id')) {
                    $table->unsignedBigInteger('competitor_group_id')->nullable();
                }
            });
        }

        if (Schema::hasTable('x1_participants')) {
            Schema::table('x1_participants', function (Blueprint $table) {
                if (!Schema::hasColumn('x1_participants', 'competitor_group_id')) {
                    $table->unsignedBigInteger('competitor_group_id')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('x1_participants')) {
            Schema::table('x1_participants', function (Blueprint $table) {
                if (Schema::hasColumn('x1_participants', 'competitor_group_id')) {
                    $table->dropColumn('competitor_group_id');
                }
            });
        }

        if (Schema::hasTable('x1_rooms')) {
            Schema::table('x1_rooms', function (Blueprint $table) {
                if (Schema::hasColumn('x1_rooms', 'competitor_group_id')) {
                    $table->dropColumn('competitor_group_id');
                }
            });
        }
    }
};
