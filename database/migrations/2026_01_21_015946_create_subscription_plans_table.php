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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Premium Mensal", "Premium Semestral", "Premium Anual"
            $table->string('slug')->unique();                // "mensal", "semestral", "anual"
            $table->decimal('price', 10, 2);                 // 49.90, 249.90, 499.90
            $table->decimal('original_price', 10, 2)->nullable(); // Preço original (sem desconto)
            $table->integer('duration_days');                // 30, 180, 365
            $table->integer('trial_days')->default(0);       // 30 para mensal (novos), 0 para outros
            $table->string('billing_cycle');                 // "monthly", "semiannual", "annual"
            $table->text('description')->nullable();
            $table->json('features')->nullable();            // Lista de benefícios
            $table->string('badge')->nullable();             // "Mais flexível", "Popular", "Melhor oferta"
            $table->string('badge_color')->nullable();       // Cor do badge
            $table->boolean('is_featured')->default(false);  // Destaque visual
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
