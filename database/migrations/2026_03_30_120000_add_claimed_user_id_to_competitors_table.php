<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitors', function (Blueprint $table) {
            if (!Schema::hasColumn('competitors', 'claimed_user_id')) {
                $table->foreignId('claimed_user_id')
                    ->nullable()
                    ->unique()
                    ->after('profile_claimed')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('competitors', function (Blueprint $table) {
            if (Schema::hasColumn('competitors', 'claimed_user_id')) {
                $table->dropConstrainedForeignId('claimed_user_id');
            }
        });
    }
};
