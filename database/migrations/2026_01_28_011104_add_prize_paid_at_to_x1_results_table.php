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
            $table->timestamp('prize_paid_at')->nullable()->after('processed_at');
            $table->unsignedBigInteger('prize_paid_by')->nullable()->after('prize_paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('x1_results', function (Blueprint $table) {
            $table->dropColumn(['prize_paid_at', 'prize_paid_by']);
        });
    }
};
