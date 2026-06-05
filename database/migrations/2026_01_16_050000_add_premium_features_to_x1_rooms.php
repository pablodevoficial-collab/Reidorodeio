<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('x1_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('x1_rooms', 'is_premium_room')) {
                $table->boolean('is_premium_room')->default(false)->after('fee_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('x1_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('x1_rooms', 'is_premium_room')) {
                $table->dropColumn('is_premium_room');
            }
        });
    }
};
