<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\X1Payment;
use App\Services\MercadoPagoService;
use App\Services\X1PaymentSettlementService;
use Illuminate\Support\Facades\DB;

class ProcessX1PaymentManual extends Command
{
    protected $signature = 'x1:process-payment {payment_id : MercadoPago Payment ID}';
    protected $description = 'Processar pagamento X1 manualmente via MercadoPago Payment ID';

    public function handle()
    {
        $paymentId = $this->argument('payment_id');
        
        $this->info("🔍 Buscando pagamento no MercadoPago: {$paymentId}");
        
        $mp = app(MercadoPagoService::class);
        
        try {
            $payment = $mp->fetchPayment($paymentId);
        } catch (\Exception $e) {
            $this->error("❌ Erro ao buscar pagamento: " . $e->getMessage());
            return 1;
        }
        
        $this->info("✅ Pagamento encontrado");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $payment['id'] ?? 'N/A'],
                ['Status', $payment['status'] ?? 'N/A'],
                ['External Ref', $payment['external_reference'] ?? 'N/A'],
                ['Amount', $payment['transaction_amount'] ?? 'N/A'],
            ]
        );
        
        $externalRef = $payment['external_reference'] ?? null;
        if (!$externalRef) {
            $this->error('❌ Sem external_reference');
            return 1;
        }
        
        $paymentStatus = $payment['status'] ?? 'pending';
        
        DB::transaction(function () use ($externalRef, $paymentId, $paymentStatus, $payment) {
            $x1Payment = X1Payment::where('external_reference', $externalRef)->first();
            if (!$x1Payment) {
                $this->error('❌ X1Payment não encontrado');
                return;
            }
            
            $this->info("📦 X1Payment encontrado: ID {$x1Payment->id}, Room {$x1Payment->x1_room_id}, Role {$x1Payment->role}");

            $originalCompetitorId = data_get($x1Payment->payload, 'competitor_id');
            $originalGroupId = data_get($x1Payment->payload, 'competitor_group_id');

            unset($payment['competitor_id'], $payment['competitor_group_id']);

            $x1Payment->provider_payment_id = (string) $paymentId;
            $x1Payment->status = $paymentStatus;
            $existingPayload = is_array($x1Payment->payload) ? $x1Payment->payload : [];
            $x1Payment->payload = array_merge($existingPayload, $payment, [
                'competitor_id' => $originalCompetitorId,
                'competitor_group_id' => $originalGroupId,
            ]);
            if ($paymentStatus === 'approved') {
                $x1Payment->paid_at = now();
            }
            $x1Payment->save();
            
            $this->info("✅ X1Payment atualizado");
            
            if ($paymentStatus !== 'approved') {
                $this->warn("⏳ Status não é 'approved': {$paymentStatus}");
                return;
            }
            
            $result = app(X1PaymentSettlementService::class)->settleApprovedPayment($x1Payment);
            $this->info('🧾 Resultado da liquidação: ' . ($result['message'] ?? ($result['outcome'] ?? 'ok')));
        });
        
        $this->info('✅ Processamento concluído!');
        return 0;
    }
}
