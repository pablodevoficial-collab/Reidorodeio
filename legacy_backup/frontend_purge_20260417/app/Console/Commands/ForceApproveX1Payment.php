<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\X1Payment;
use App\Services\X1PaymentSettlementService;
use Illuminate\Support\Facades\DB;

class ForceApproveX1Payment extends Command
{
    protected $signature = 'x1:force-approve {payment_record_id : ID do registro na tabela x1_payments}';
    protected $description = '⚠️ FORÇAR aprovação de pagamento X1 (APENAS PARA TESTES!)';

    public function handle()
    {
        $this->warn('⚠️ ATENÇÃO: Este comando FORÇA a aprovação ignorando o MercadoPago!');
        $this->warn('⚠️ Use APENAS para testes! Não use em produção com dinheiro real!');
        
        if (!$this->confirm('Tem certeza que quer forçar a aprovação?')) {
            $this->info('Cancelado.');
            return 0;
        }
        
        $paymentId = $this->argument('payment_record_id');
        
        $x1Payment = X1Payment::find($paymentId);
        if (!$x1Payment) {
            $this->error("❌ Pagamento #{$paymentId} não encontrado");
            return 1;
        }
        
        $this->info("📦 Pagamento encontrado:");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $x1Payment->id],
                ['Room', $x1Payment->x1_room_id],
                ['User', $x1Payment->user_id],
                ['Role', $x1Payment->role],
                ['Amount', 'R$ ' . $x1Payment->amount],
                ['Status Atual', $x1Payment->status],
            ]
        );
        
        DB::transaction(function () use ($x1Payment) {
            $x1Payment->status = 'approved';
            $x1Payment->paid_at = now();
            $x1Payment->save();
            
            $this->info('✅ Status alterado para "approved"');
            
            $result = app(X1PaymentSettlementService::class)->settleApprovedPayment($x1Payment);
            $this->info('🧾 Resultado da liquidação: ' . ($result['message'] ?? ($result['outcome'] ?? 'ok')));
        });
        
        $this->info('✅ Aprovação forçada com sucesso!');
        $this->warn('⚠️ Lembre-se: isso foi FORÇADO localmente, não veio do MercadoPago!');
        
        return 0;
    }
}
