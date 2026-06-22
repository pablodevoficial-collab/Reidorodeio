<?php

namespace App\Jobs;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job para atualizar tiers de afiliados (verifica se alcançou novos níveis)
 */
class UpdateAffiliateTiers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $updated = 0;
        
        // Buscar todos afiliados ativos
        $affiliates = Affiliate::where('status', 'active')->get();

        foreach ($affiliates as $affiliate) {
            $oldTier = $affiliate->tier;
            $affiliate->updateTier();
            
            if ($affiliate->tier !== $oldTier) {
                $updated++;
                Log::info("[Affiliate] Tier upgraded", [
                    'affiliate_id' => $affiliate->id,
                    'old_tier' => $oldTier,
                    'new_tier' => $affiliate->tier,
                    'referrals' => $affiliate->active_referrals
                ]);
            }
        }

        Log::info("[Affiliate Job] Updated affiliate tiers", [
            'total_updated' => $updated
        ]);

        return $updated;
    }
}
