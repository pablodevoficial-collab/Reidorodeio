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
        Schema::create('affiliate_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('paid_by_admin_id')->comment('ID do admin que efetuou pagamento');
            
            $table->decimal('amount', 12, 2)->comment('Valor pago');
            $table->text('notes')->nullable()->comment('Observações do admin (método Pix, comprovante, etc)');
            
            $table->timestamps();
            
            // Índices
            $table->index('affiliate_id', 'idx_payments_affiliate');
            $table->index('created_at', 'idx_payments_created');
            
            // Foreign key para admin (se tabela existir)
            // Comentado para evitar erro
            // $table->foreign('paid_by_admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_payments');
    }
};
