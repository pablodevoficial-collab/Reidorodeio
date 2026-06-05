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
            // Adicionar colunas de afiliado APÓS 'id'
            // referred_by_id aponta para o USER_ID do afiliado, não para affiliates.id
            $table->foreignId('referred_by_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            $table->timestamp('referred_at')->nullable()->after('referred_by_id')->comment('Data de registro via referral');
            
            // Índice
            $table->index('referred_by_id', 'idx_users_referred_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by_id']);
            $table->dropIndex('idx_users_referred_by');
            $table->dropColumn(['referred_by_id', 'referred_at']);
        });
    }
};
