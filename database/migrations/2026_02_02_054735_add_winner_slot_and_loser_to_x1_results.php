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
        Schema::table('x1_results', function (Blueprint $table) {
            $table->tinyInteger('winner_slot')->nullable()->after('winner_user_id')->comment('1=host, 2=opponent');
            $table->unsignedBigInteger('loser_user_id')->nullable()->after('winner_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('x1_results', function (Blueprint $table) {
            $table->dropColumn(['winner_slot', 'loser_user_id']);
        });
    }
};
