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
        Schema::table('subscriptions', function (Blueprint $table) {
            // CPF usado no trial (para evitar múltiplos trials)
            $table->string('trial_cpf', 14)->nullable()->after('is_trial');
            
            // Índice para busca rápida de trials por CPF
            $table->index('trial_cpf');
        });

        // Tabela para controle global de CPFs que já usaram trial
        if (!Schema::hasTable('subscription_trial_cpfs')) {
            Schema::create('subscription_trial_cpfs', function (Blueprint $table) {
                $table->id();
                $table->string('cpf', 14)->unique();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
                $table->timestamp('used_at');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['trial_cpf']);
            $table->dropColumn('trial_cpf');
        });

        Schema::dropIfExists('subscription_trial_cpfs');
    }
};
