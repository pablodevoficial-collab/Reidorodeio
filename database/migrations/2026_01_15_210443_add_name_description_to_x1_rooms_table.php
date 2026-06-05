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
        Schema::table('x1_rooms', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->text('description')->nullable()->after('name');
            $table->json('metadata')->nullable()->after('competitor_group_id');
            $table->timestamp('closed_at')->nullable()->after('data_fim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('x1_rooms', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'metadata', 'closed_at']);
        });
    }
};
