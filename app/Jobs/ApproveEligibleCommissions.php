<?php

namespace App\Jobs;

use App\Models\AffiliateCommission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job para aprovar comissões elegíveis (após 7 dias)
 */
class ApproveEligibleCommissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $approved = 0;
        
        // Buscar comissões elegíveis (7 dias passados)
        $commissions = AffiliateCommission::eligible()->get();

        foreach ($commissions as $commission) {
            if ($commission->approve()) {
                $approved++;
            }
        }

        Log::info("[Affiliate Job] Approved eligible commissions", [
            'total_approved' => $approved
        ]);

        return $approved;
    }
}
