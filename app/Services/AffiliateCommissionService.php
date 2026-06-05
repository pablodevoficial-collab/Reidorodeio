<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Serviço para processar comissões de afiliados
 * Integra com X1 e Fantasy
 */
class AffiliateCommissionService
{
    /**
     * Processar comissão de sala X1 sobre o lucro da casa.
     * Se ambos os lados tiverem afiliados ativos diferentes, divide o pool igualmente.
     */
    public function processX1Commission(int $roomId, int $winnerId, int $loserId, float $platformFee): bool
    {
        Log::info('[Affiliate] X1 commissions disabled: bolao-only affiliate program', [
            'room_id' => $roomId,
            'winner_id' => $winnerId,
            'loser_id' => $loserId,
            'platform_fee' => $platformFee,
        ]);

        return false;
    }

    /**
     * Criar registro de comissão (método auxiliar)
     */
    private function createCommission(
        Affiliate $affiliate,
        int $userId,
        int $sourceId,
        float $commissionAmount,
        string $type,
        float $tierPercent,
        ?float $baseAmount = null
    ): void
    {
        $commission = new AffiliateCommission();
        $commission->affiliate_id = $affiliate->id;
        $commission->referred_user_id = $userId;
        $commission->base_amount = $baseAmount;
        
        // Usar coluna específica baseada no tipo
        if ($type === 'x1') {
            $commission->type = 'x1_room';
            $commission->x1_room_id = $sourceId;
        } else {
            $commission->type = 'fantasy_prize';
            $commission->fantasy_team_id = $sourceId;
        }
        
        $commission->commission_amount = $commissionAmount;
        $commission->commission_percent = $tierPercent;
        $commission->status = 'pending';
        $commission->save();
        
        // Atualizar pending_commission e total_earned do afiliado
        $affiliate->addPendingCommission($commissionAmount);
    }
    
    /**
     * Obter percentual de comissão baseado no tier
     * 
     * X1: desativado
     * Fantasy: 3-7% sobre o PRÊMIO do indicado (pago pela casa)
     * 
     * @param string $tier bronze|silver|gold|diamond
     * @param string $type x1|fantasy
     * @return float
     */
    private function getTierCommissionPercent(string $tier, string $type): float
    {
        $percentages = [
            'x1' => [
                'bronze' => 20.0,
                'silver' => 25.0,
                'gold' => 30.0,
                'diamond' => 35.0,
            ],
            'fantasy' => [
                'bronze' => 3.0,
                'silver' => 5.0,
                'gold' => 6.0,
                'diamond' => 7.0,
            ]
        ];
        
        return $percentages[strtolower($type)][strtolower($tier)] ?? 0.0;
    }

    /**
     * Processar comissão de Fantasy quando usuário GANHA prêmio
     * 
     * @param int $teamId ID da equipe vencedora
     * @param int $ownerId ID do dono da equipe
     * @param float $prizeAmount Valor do prêmio ganho
     * @return bool
     */
    public function processFantasyCommission(int $teamId, int $ownerId, float $prizeAmount): bool
    {
        try {
            $owner = User::find($ownerId);
            
            // Verificar se o dono foi indicado por alguém
            if (!$owner || !$owner->referred_by_id) {
                Log::info("[Affiliate] Fantasy: Ganhador não foi indicado", [
                    'team_id' => $teamId,
                    'owner_id' => $ownerId,
                ]);
                return false;
            }

            // Buscar afiliado que indicou (usando user_id)
            $affiliate = Affiliate::where('user_id', $owner->referred_by_id)->first();

            if ($affiliate && $affiliate->status !== 'active') {
                $affiliate = null;
            }

            if (!$affiliate) {
                Log::info("[Affiliate] Fantasy: Afiliado não encontrado ou inativo", [
                    'team_id' => $teamId,
                    'owner_id' => $ownerId,
                    'referred_by_id' => $owner->referred_by_id,
                ]);
                return false;
            }

            // Calcular comissão baseado no tier do afiliado (sobre o PRÊMIO)
            $commissionPercent = $this->getTierCommissionPercent($affiliate->tier, 'fantasy');
            $commissionAmount = $this->floorMoney(($prizeAmount * $commissionPercent) / 100);

            // Criar registro de comissão
            $this->createCommission($affiliate, $ownerId, $teamId, $commissionAmount, 'fantasy', $commissionPercent);

            Log::info("[Affiliate] Fantasy: Comissão registrada", [
                'affiliate_id' => $affiliate->id,
                'team_id' => $teamId,
                'owner_id' => $ownerId,
                'prize_amount' => $prizeAmount,
                'commission_amount' => $commissionAmount,
                'tier' => $affiliate->tier,
                'tier_percent' => $commissionPercent,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("[Affiliate] Error processing Fantasy commission", [
                'error' => $e->getMessage(),
                'team_id' => $teamId,
                'owner_id' => $ownerId,
            ]);
            return false;
        }
    }

    private function floorMoney(float $amount): float
    {
        return floor($amount * 100) / 100;
    }

    /**
     * Registrar nova indicação (incrementar contador)
     * 
     * @param int $affiliateUserId ID do usuário afiliado
     * @return bool
     */
    public function registerReferral(int $affiliateUserId): bool
    {
        try {
            $affiliate = Affiliate::where('user_id', $affiliateUserId)->first();
            
            if (!$affiliate) {
                return false;
            }

            $affiliate->addReferral();
            
            Log::info("[Affiliate] Referral registered", [
                'affiliate_id' => $affiliate->id,
                'new_referral_count' => $affiliate->active_referrals
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("[Affiliate] Error registering referral", [
                'error' => $e->getMessage(),
                'affiliate_user_id' => $affiliateUserId
            ]);
            return false;
        }
    }
}
