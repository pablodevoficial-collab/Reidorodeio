<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\X1Payment;
use App\Services\MercadoPagoService;
use App\Services\X1PaymentSettlementService;
use Carbon\Carbon;

/**
 * Processa pagamentos X1 pendentes via cron
 * 
 * Ideal para shared hosting onde não há queue workers
 * Rode a cada 1-2 minutos via cron
 */
class ProcessPendingX1Payments extends Command
{
    protected $signature = 'x1:process-payments 
                            {--limit=20 : Número máximo de pagamentos a processar}
                            {--dry-run : Apenas mostra o que seria processado}';

    protected $description = 'Processa pagamentos X1 pendentes verificando status no Mercado Pago';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        
        $this->info("Buscando pagamentos pendentes (limit: {$limit})...");
        
        // Busca pagamentos pendentes criados há mais de 30 segundos
        // e menos de 30 minutos (evita processar muito antigos)
        $payments = X1Payment::where('status', 'pending')
            ->where('created_at', '<=', Carbon::now()->subSeconds(30))
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->whereNotNull('provider_preference_id')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
            
        if ($payments->isEmpty()) {
            $this->info('Nenhum pagamento pendente encontrado.');
            return 0;
        }
        
        $this->info("Encontrados {$payments->count()} pagamentos pendentes.");
        
        $processed = 0;
        $approved = 0;
        $failed = 0;
        
        foreach ($payments as $payment) {
            $this->line("  Verificando payment #{$payment->id} (preference: {$payment->provider_preference_id})");
            
            if ($dryRun) {
                $processed++;
                continue;
            }
            
            try {
                $mp = new MercadoPagoService();
                
                // Busca pagamentos associados à preference
                $mpPayments = $mp->searchPaymentsByPreference($payment->provider_preference_id);
                
                if (empty($mpPayments)) {
                    $this->line("    -> Nenhum pagamento encontrado no MP");
                    continue;
                }
                
                foreach ($mpPayments as $mpPayment) {
                    if ($mpPayment['status'] === 'approved') {
                        $this->line("    -> Pagamento aprovado! Atualizando...");
                        
                        $payment->update([
                            'status' => 'approved',
                            'provider_payment_id' => $mpPayment['id'] ?? null,
                            'paid_at' => now(),
                        ]);
                        
                        // Processar aprovação diretamente
                        $result = app(X1PaymentSettlementService::class)->settleApprovedPayment($payment);
                        $this->line("    -> Resultado: " . ($result['message'] ?? ($result['outcome'] ?? 'ok')));
                        
                        $approved++;
                        break;
                    } elseif (in_array($mpPayment['status'], ['rejected', 'cancelled', 'refunded'])) {
                        $this->line("    -> Pagamento {$mpPayment['status']}");
                        
                        $payment->update([
                            'status' => $mpPayment['status'],
                            'provider_payment_id' => $mpPayment['id'] ?? null,
                        ]);
                        
                        $failed++;
                        break;
                    }
                }
                
                $processed++;
                
            } catch (\Exception $e) {
                $this->error("    -> Erro: " . $e->getMessage());
            }
            
            // Pequena pausa para não sobrecarregar API
            usleep(100000); // 100ms
        }
        
        $this->info("Processamento concluído:");
        $this->info("  - Total processados: {$processed}");
        $this->info("  - Aprovados: {$approved}");
        $this->info("  - Falhas: {$failed}");
        
        return 0;
    }
    
}
