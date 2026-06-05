<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\BotUser;
use App\Models\FantasyLeague;
use App\Models\FantasyTeam;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service para finalizar ligas Fantasy e distribuir prêmios
 */
class FinalizeFantasyLeagueService
{
    protected AffiliateCommissionService $affiliateService;

    public function __construct(AffiliateCommissionService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    /** Percentual base de inscritos que recebem prêmio */
    private const PAID_PERCENT = 10;

    /**
     * Calcular quantas posições são pagas.
     * Regra:
     * - sempre 10% dos inscritos (mín 1)
     * DEVE ser idêntico ao frontend (draft-arena.js → getPaidPositions)
     */
    private function getPaidPositions(FantasyLeague $league, int $totalPlayers): int
    {
        if ($totalPlayers <= 0) return 0;

        $override = (int) ($league->paid_positions_override ?? 0);
        $maxUsers = (int) ($league->max_users ?? 0);

        if ($override > 0) {
            if ($maxUsers <= 0 || $totalPlayers >= $maxUsers) {
                return min($override, $totalPlayers);
            }
        }

        return max(1, (int) floor($totalPlayers * self::PAID_PERCENT / 100));
    }

    private function normalizeDistributionForPaidPositions(array $distribution, int $paidPositions): array
    {
        if ($paidPositions <= 0) {
            return [];
        }

        $normalized = [];
        foreach ($distribution as $position => $percent) {
            $pos = (int) $position;
            $pct = (float) $percent;
            if ($pos < 1 || $pos > $paidPositions || $pct < 0) {
                continue;
            }
            $normalized[$pos] = $pct;
        }

        if (empty($normalized)) {
            return [];
        }

        ksort($normalized);
        $sum = array_sum($normalized);
        if ($sum <= 0) {
            return [];
        }

        foreach ($normalized as $position => $percent) {
            $normalized[$position] = round(($percent / $sum) * 100, 2);
        }

        $finalSum = array_sum($normalized);
        if (abs($finalSum - 100.0) > 0.01 && isset($normalized[1])) {
            $normalized[1] = round($normalized[1] + (100.0 - $finalSum), 2);
        }

        return $normalized;
    }

    /**
     * Gerar faixas de distribuição dinâmicas.
     * Retorna array de ['from' => int, 'to' => int, 'pct' => float]
     * onde pct é a % total do pool para toda a faixa.
     */
    private function generateTiers(int $paidPositions): array
    {
        if ($paidPositions <= 0) return [];
        if ($paidPositions === 1) return [['from' => 1, 'to' => 1, 'pct' => 100.0]];
        if ($paidPositions === 2) return [['from' => 1, 'to' => 1, 'pct' => 65.0], ['from' => 2, 'to' => 2, 'pct' => 35.0]];
        if ($paidPositions === 3) return [['from' => 1, 'to' => 1, 'pct' => 50.0], ['from' => 2, 'to' => 2, 'pct' => 30.0], ['from' => 3, 'to' => 3, 'pct' => 20.0]];

        // Faixas: 1º, 2º, 3º + grupos crescentes
        $tiers = [
            ['from' => 1, 'to' => 1],
            ['from' => 2, 'to' => 2],
            ['from' => 3, 'to' => 3],
        ];

        $remaining = $paidPositions - 3;
        $pos = 4;

        if ($remaining <= 3) {
            $tiers[] = ['from' => 4, 'to' => $paidPositions];
        } else {
            $chunks = $remaining <= 8 ? 2 : ($remaining <= 20 ? 3 : 4);
            $base = (int) floor($remaining / $chunks);
            $extra = $remaining - $base * $chunks;
            $sizes = [];
            for ($c = 0; $c < $chunks; $c++) {
                $sizes[] = $base + ($c < $extra ? 1 : 0);
            }
            sort($sizes);
            foreach ($sizes as $sz) {
                $tiers[] = ['from' => $pos, 'to' => $pos + $sz - 1];
                $pos += $sz;
            }
        }

        // Distribuir %: floor+curve (floor garante ~1.1x entrada, curve escala com tamanho)
        $nTiers = count($tiers);
        $floorPctPerPerson = 100.0 / ($paidPositions * 3.6);
        $totalFloor = $floorPctPerPerson * $paidPositions;
        $curvePool = 100.0 - $totalFloor;

        $spread = max(3, pow($paidPositions, 1.2));
        $ratio = pow($spread, 1.0 / max(1, $nTiers - 1));

        $perPerson = array_fill(0, $nTiers, 0);
        $perPerson[$nTiers - 1] = 1;
        for ($i = $nTiers - 2; $i >= 0; $i--) {
            $perPerson[$i] = $perPerson[$i + 1] * $ratio;
        }

        $totalRaw = 0;
        for ($i = 0; $i < $nTiers; $i++) {
            $count = $tiers[$i]['to'] - $tiers[$i]['from'] + 1;
            $totalRaw += $perPerson[$i] * $count;
        }

        // Combinar floor + curve por faixa
        for ($i = 0; $i < $nTiers; $i++) {
            $count = $tiers[$i]['to'] - $tiers[$i]['from'] + 1;
            $curvePctPerPerson = $curvePool * $perPerson[$i] / $totalRaw;
            $totalPctPerPerson = $floorPctPerPerson + $curvePctPerPerson;
            $tiers[$i]['pct'] = round($totalPctPerPerson * $count, 2);
        }

        $sum = array_sum(array_column($tiers, 'pct'));
        if (abs($sum - 100.0) > 0.01) {
            $tiers[0]['pct'] = round($tiers[0]['pct'] + (100.0 - $sum), 2);
        }

        return $tiers;
    }

    /**
     * Converter faixas em mapa posição → percentual individual (para distributePrizes)
     */
    private function generateDistribution(int $paidPositions): array
    {
        $tiers = $this->generateTiers($paidPositions);
        $dist = [];
        foreach ($tiers as $tier) {
            $count = $tier['to'] - $tier['from'] + 1;
            $pctPerPerson = round($tier['pct'] / $count, 2);
            for ($pos = $tier['from']; $pos <= $tier['to']; $pos++) {
                $dist[$pos] = $pctPerPerson;
            }
        }
        // Ajustar para somar 100
        $sum = array_sum($dist);
        if (abs($sum - 100.0) > 0.01) {
            $dist[1] = round($dist[1] + (100.0 - $sum), 2);
        }
        return $dist;
    }

    /**
     * Finalizar uma liga Fantasy e distribuir prêmios
     * 
     * @param FantasyLeague $league
     * @param int|null $adminId ID do admin que finalizou
     * @return array Resultado da operação
     */
    public function finalize(FantasyLeague $league, ?int $adminId = null): array
    {
        if ($league->status === 'finalized') {
            return [
                'success' => false,
                'error' => 'Liga já foi finalizada anteriormente',
            ];
        }

        try {
            return DB::transaction(function () use ($league, $adminId) {
                // 1. Obter ranking final ordenado por pontos
                $teams = $league->teams()
                    ->where('is_active', true)
                    ->orderByDesc('total_points')
                    ->get();

                if ($teams->isEmpty()) {
                    return [
                        'success' => false,
                        'error' => 'Nenhum time ativo nesta liga',
                    ];
                }

                // 2. Calcular prize pool total
                $prizePool = $this->calculatePrizePool($league);

                if ($prizePool <= 0) {
                    // Liga sem prêmio (só pontos) - apenas finaliza ranking
                    $this->updateRankingPositions($teams);
                    $this->markLeagueFinalized($league, $adminId, 0.0);

                    return [
                        'success' => true,
                        'message' => 'Liga finalizada (sem prêmios monetários)',
                        'ranking' => $this->formatRanking($teams),
                        'prize_pool' => 0,
                        'prizes_paid' => [],
                        'commissions_paid' => [],
                    ];
                }

                // 3. Obter distribuição de prêmios
                $distribution = $this->getPrizeDistribution($league);

                if (empty($distribution)) {
                    // Nenhum inscrito ativo
                    $this->updateRankingPositions($teams);
                    $this->markLeagueFinalized($league, $adminId, $prizePool);

                    Log::info('[Fantasy] Liga finalizada sem distribuição (sem inscritos ativos)', [
                        'league_id' => $league->id,
                        'teams_count' => $teams->count(),
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Liga finalizada (sem distribuição de prêmios)',
                        'ranking' => $this->formatRanking($teams),
                        'prize_pool' => $prizePool,
                        'prizes_paid' => [],
                        'commissions_paid' => [],
                    ];
                }

                // 4. Distribuir prêmios e processar comissões
                $results = $this->distributePrizes($teams, $prizePool, $distribution, $league);

                // 5. Marcar liga como finalizada
                $this->markLeagueFinalized($league, $adminId, $prizePool);

                Log::info('[Fantasy] Liga finalizada com sucesso', [
                    'league_id' => $league->id,
                    'league_name' => $league->name,
                    'prize_pool' => $prizePool,
                    'teams_count' => $teams->count(),
                    'prizes_paid' => count($results['prizes']),
                    'commissions_paid' => count($results['commissions']),
                    'total_commissions' => $results['total_commissions'],
                ]);

                return [
                    'success' => true,
                    'message' => 'Liga finalizada e prêmios distribuídos',
                    'ranking' => $this->formatRanking($teams),
                    'prize_pool' => $prizePool,
                    'prizes_paid' => $results['prizes'],
                    'commissions_paid' => $results['commissions'],
                    'total_commissions' => $results['total_commissions'],
                ];
            });

        } catch (\Exception $e) {
            Log::error('[Fantasy] Erro ao finalizar liga', [
                'league_id' => $league->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Erro ao finalizar liga: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calcular prize pool da liga
     */
    private function calculatePrizePool(FantasyLeague $league): float
    {
        if (($league->reward_mode ?? 'computed') === 'manual_prize') {
            if (($league->prize_type ?? 'money') === 'money' && $league->manual_prize_pool !== null) {
                return max(0, (float) $league->manual_prize_pool);
            }

            return 0.0;
        }

        // Para ligas pagas/computadas, a finalização sempre usa o tamanho real do bolão.
        if (!$league->is_premium) {
            $teamsCount = $league->teams()->where('is_active', true)->count();
            $entryPrice = (float) ($league->price ?? 0);
            if ($entryPrice <= 0) {
                return 0.0;
            }
            $houseCut = (float) ($league->house_cut_percent ?? 10);

            $totalCollection = $teamsCount * $entryPrice;
            $prizePool = $totalCollection * (1 - $houseCut / 100);

            return max(0, $prizePool);
        }

        // Calcular baseado nas entradas
        $teamsCount = $league->teams()->where('is_active', true)->count();
        $entryPrice = (float) ($league->price ?? 0);
        $houseCut = (float) ($league->house_cut_percent ?? 10);

        $totalCollection = $teamsCount * $entryPrice;
        $prizePool = $totalCollection * (1 - $houseCut / 100);

        return max(0, $prizePool);
    }

    /**
     * Obter distribuição de prêmios alinhada com o frontend
     * Usa distribuição decrescente dinâmica baseada em 10% dos inscritos
     */
    private function getPrizeDistribution(FantasyLeague $league): array
    {
        $totalTeams = $league->teams()->where('is_active', true)->count();
        $paidPositions = $this->getPaidPositions($league, $totalTeams);
        if ($paidPositions <= 0) {
            return [];
        }

        if (!empty($league->prize_distribution)) {
            $dist = is_string($league->prize_distribution)
                ? json_decode($league->prize_distribution, true)
                : $league->prize_distribution;

            if (is_array($dist) && !empty($dist)) {
                $normalized = $this->normalizeDistributionForPaidPositions($dist, $paidPositions);
                if (!empty($normalized)) {
                    return $normalized;
                }
            }
        }

        return $this->generateDistribution($paidPositions);
    }

    /**
     * Distribuir prêmios para os times
     */
    private function distributePrizes(
        $teams, 
        float $prizePool, 
        array $distribution, 
        FantasyLeague $league
    ): array {
        $results = [
            'prizes' => [],
            'commissions' => [],
            'total_commissions' => 0.0,
        ];

        $position = 1;
        foreach ($teams as $team) {
            // Atualizar posição final
            $team->final_position = $position;

            // Verificar se esta posição tem prêmio
            $prizePercent = $distribution[$position] ?? 0;
            
            if ($prizePercent > 0) {
                $grossPrizeAmount = $this->floorMoney(($prizePool * $prizePercent) / 100);

                // A comissão do afiliado sai do prêmio bruto do cliente.
                $commission = $this->processAffiliateCommission($team, $grossPrizeAmount, $league);
                $commissionAmount = $this->floorMoney((float) ($commission['amount'] ?? 0));
                $netPrizeAmount = max(0, $this->floorMoney($grossPrizeAmount - $commissionAmount));

                // Pagar prêmio líquido ao usuário
                $this->payPrize($team, $netPrizeAmount, $grossPrizeAmount, $commissionAmount);

                $results['prizes'][] = [
                    'position' => $position,
                    'team_id' => $team->id,
                    'user_id' => $team->user_id,
                    'user_name' => $team->user->name ?? 'Usuário',
                    'team_name' => $team->team_name,
                    'points' => $team->total_points,
                    'prize' => $netPrizeAmount,
                    'gross_prize' => $grossPrizeAmount,
                    'commission_amount' => $commissionAmount,
                ];
                if ($commission) {
                    $results['commissions'][] = $commission;
                    $results['total_commissions'] += $commission['amount'];
                }
            }

            $team->save();
            $position++;
        }

        return $results;
    }

    /**
     * Pagar prêmio ao usuário
     */
    private function payPrize(FantasyTeam $team, float $amount, float $grossAmount = 0, float $commissionAmount = 0): void
    {
        $team->prize_won = $amount;
        $team->prize_paid_at = now();

        if ($team->user_id) {
            // Creditar no saldo do usuário
            DB::table('users')
                ->where('id', $team->user_id)
                ->increment('balance', $amount);

            // Atualizar total de ganhos no perfil
            DB::table('users')
                ->where('id', $team->user_id)
                ->increment('total_earnings', $amount);

            // Registrar transação (se tabela existir)
            if (\Schema::hasTable('transactions')) {
                DB::table('transactions')->insert([
                    'user_id' => $team->user_id,
                    'trx_type' => '+',
                    'trx' => 'FANTASY_' . strtoupper(uniqid()),
                    'amount' => $amount,
                    'charge' => 0,
                    'post_balance' => DB::table('users')->where('id', $team->user_id)->value('balance'),
                    'details' => $commissionAmount > 0
                        ? "Prêmio Fantasy líquido - {$team->final_position}º lugar - Liga #{$team->fantasy_league_id} (bruto R$ {$grossAmount}, comissão R$ {$commissionAmount})"
                        : "Prêmio Fantasy - {$team->final_position}º lugar - Liga #{$team->fantasy_league_id}",
                    'remark' => 'fantasy_prize',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } elseif ($team->bot_user_id) {
            // Bot ganhou - apenas logar
            Log::info('[Fantasy] Bot venceu prêmio (sem crédito em saldo)', [
                'bot_id' => $team->bot_user_id,
                'amount' => $amount
            ]);
        }

        Log::info('[Fantasy] Prêmio pago', [
            'team_id' => $team->id,
            'user_id' => $team->user_id ?? 'BOT-'.$team->bot_user_id,
            'position' => $team->final_position,
            'amount' => $amount,
            'gross_amount' => $grossAmount,
            'commission_amount' => $commissionAmount,
        ]);
    }

    /**
     * Processar comissão do afiliado descontando do prêmio bruto do cliente.
     */
    private function processAffiliateCommission(
        FantasyTeam $team, 
        float $prizeAmount, 
        FantasyLeague $league
    ): ?array {
        $referrerId = null;
        $referredUserId = null;

        if ($team->user_id) {
            $user = User::find($team->user_id);
            if ($user && $user->referred_by_id) {
                $referrerId = $user->referred_by_id;
                $referredUserId = $user->id;
            }
        } elseif ($team->bot_user_id) {
            $bot = BotUser::find($team->bot_user_id);
            if ($bot && $bot->referred_by_id) {
                $referrerId = $bot->referred_by_id;
                // referred_user_id fica null para bots
            }
        }

        if (!$referrerId) {
            return null;
        }

        // Buscar afiliado ativo
        $affiliate = Affiliate::where('user_id', $referrerId)
            ->where('status', 'active')
            ->first();

        if (!$affiliate) {
            return null;
        }

        // Calcular comissão (2-5% sobre o prêmio, PAGO PELA CASA)
        $commissionPercent = $this->getFantasyCommissionPercent($affiliate->tier);
        $commissionAmount = $this->floorMoney(($prizeAmount * $commissionPercent) / 100);

        if ($commissionAmount <= 0) {
            return null;
        }

        // Criar registro de comissão
        $commission = new AffiliateCommission();
        $commission->affiliate_id = $affiliate->id;
        $commission->referred_user_id = $referredUserId;
        $commission->type = 'fantasy_prize';
        $commission->fantasy_team_id = $team->id;
        $commission->base_amount = $prizeAmount;
        $commission->commission_amount = $commissionAmount;
        $commission->commission_percent = $commissionPercent;
        $commission->status = 'approved'; // Auto-aprovado para Fantasy
        $commission->approved_at = now();
        $commission->save();

        // Incrementar saldo pendente e total ganho do afiliado
        $affiliate->addPendingCommission($commissionAmount);

        // TODO: Enviar notificação ao afiliado
        // "Seu indicado ganhou R$ {prizeAmount} e você recebeu R$ {commissionAmount}!"

        Log::info('[Fantasy] Comissão de afiliado processada', [
            'affiliate_id' => $affiliate->id,
            'affiliate_user_id' => $affiliate->user_id,
            'referred_user_id' => $referredUserId ?? 'BOT',
            'team_id' => $team->id,
            'prize_amount' => $prizeAmount,
            'commission_percent' => $commissionPercent,
            'commission_amount' => $commissionAmount,
        ]);

        return [
            'affiliate_id' => $affiliate->id,
            'affiliate_user_id' => $affiliate->user_id,
            'referred_user_id' => $referredUserId,
            'prize_amount' => $prizeAmount,
            'net_prize_amount' => max(0, $this->floorMoney($prizeAmount - $commissionAmount)),
            'percent' => $commissionPercent,
            'amount' => $commissionAmount,
        ];
    }

    /**
     * Obter percentual de comissão Fantasy por tier
     */
    private function getFantasyCommissionPercent(string $tier): float
    {
        $normalizedTier = strtolower($tier);

        // Fonte de verdade: tabela de tiers configurável no admin.
        if (\Schema::hasTable('affiliate_tiers')) {
            $dbPercent = DB::table('affiliate_tiers')
                ->where('tier', $normalizedTier)
                ->value('fantasy_commission_percent');

            if ($dbPercent !== null) {
                return (float) $dbPercent;
            }
        }

        // Fallback alinhado com regras públicas exibidas no Hub.
        $percentages = [
            'bronze' => 3.0,
            'silver' => 5.0,
            'gold' => 6.0,
            'diamond' => 7.0,
        ];

        return $percentages[$normalizedTier] ?? 3.0;
    }

    /**
     * Atualizar posições no ranking (sem prêmio)
     */
    private function updateRankingPositions($teams): void
    {
        $position = 1;
        foreach ($teams as $team) {
            $team->final_position = $position;
            $team->save();
            $position++;
        }
    }

    /**
     * Marcar liga como finalizada
     */
    private function markLeagueFinalized(FantasyLeague $league, ?int $adminId, ?float $finalPrizePool = null): void
    {
        $league->status = 'finalized';
        $league->finalized_at = now();
        $league->finalized_by = $adminId;
        $league->is_active = false;

        if (!$league->is_premium && ($league->reward_mode ?? 'computed') === 'computed' && $finalPrizePool !== null) {
            $league->total_prize = max(0, round($finalPrizePool, 2));
        }

        $league->save();
    }

    /**
     * Formatar ranking para retorno
     */
    private function formatRanking($teams): array
    {
        return $teams->map(function ($team) {
            return [
                'position' => $team->final_position,
                'team_id' => $team->id,
                'team_name' => $team->team_name,
                'user_id' => $team->user_id,
                'user_name' => $team->user->name ?? 'Usuário',
                'points' => $team->total_points,
                'prize' => $team->prize_won,
            ];
        })->toArray();
    }

    /**
     * Preview da finalização (sem executar)
     */
    public function preview(FantasyLeague $league): array
    {
        $teams = $league->teams()
            ->where('is_active', true)
            ->orderByDesc('total_points')
            ->with('user')
            ->get();

        $prizePool = $this->calculatePrizePool($league);
        $distribution = $this->getPrizeDistribution($league);

        $preview = [];
        $position = 1;
        foreach ($teams as $team) {
            $prizePercent = $distribution[$position] ?? 0;
            $grossPrizeAmount = $prizePercent > 0 ? $this->floorMoney(($prizePool * $prizePercent) / 100) : 0;

            // Verificar se tem afiliado
            $hasAffiliate = false;
            $commissionAmount = 0;
            $commissionPercent = 0;
            if ($team->user && $team->user->referred_by_id) {
                $affiliate = Affiliate::where('user_id', $team->user->referred_by_id)
                    ->where('status', 'active')
                    ->first();
                if ($affiliate) {
                    $hasAffiliate = true;
                    $commissionPercent = $this->getFantasyCommissionPercent($affiliate->tier);
                    $commissionAmount = $this->floorMoney(($grossPrizeAmount * $commissionPercent) / 100);
                }
            }
            $netPrizeAmount = max(0, $this->floorMoney($grossPrizeAmount - $commissionAmount));

            $preview[] = [
                'position' => $position,
                'team_id' => $team->id,
                'team_name' => $team->team_name,
                'user_id' => $team->user_id,
                'user_name' => $team->user->name ?? 'Usuário',
                'points' => $team->total_points,
                'prize_percent' => $prizePercent,
                'prize_amount' => $netPrizeAmount,
                'gross_prize_amount' => $grossPrizeAmount,
                'has_affiliate' => $hasAffiliate,
                'commission_percent' => $commissionPercent,
                'commission_amount' => $commissionAmount,
            ];

            $position++;
        }

        return [
            'league_id' => $league->id,
            'league_name' => $league->name,
            'prize_pool' => $prizePool,
            'distribution' => $distribution,
            'teams_count' => $teams->count(),
            'preview' => $preview,
        ];
    }

    private function floorMoney(float $amount): float
    {
        return floor($amount * 100) / 100;
    }
}
