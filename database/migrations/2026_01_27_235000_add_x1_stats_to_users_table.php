<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'x1_wins')) {
                $table->integer('x1_wins')->default(0)->after('balance');
            }
            if (!Schema::hasColumn('users', 'x1_losses')) {
                $table->integer('x1_losses')->default(0)->after('x1_wins');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['x1_wins', 'x1_losses']);
        });
    }
};
