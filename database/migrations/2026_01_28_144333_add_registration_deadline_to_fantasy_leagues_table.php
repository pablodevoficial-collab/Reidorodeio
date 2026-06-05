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
        Schema::table('fantasy_leagues', function (Blueprint $table) {
            // Data limite para inscrições (após isso, não pode mais criar equipes)
            $table->timestamp('registration_deadline')->nullable()->after('closes_at');
            // Flag para permitir inscrições mesmo após deadline (admin override)
            $table->boolean('allow_late_registration')->default(false)->after('registration_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fantasy_leagues', function (Blueprint $table) {
            $table->dropColumn(['registration_deadline', 'allow_late_registration']);
        });
    }
};
