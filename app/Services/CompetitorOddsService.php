<?php

namespace App\Services;

use App\Models\ModalidadeOddsSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompetitorOddsService
{
    private const ROOM_STATUSES_FOR_VOLUME = ['pending_payment', 'open', 'in_progress', 'finished'];
    private const PROFIT_SHARE_PERCENT = 30.0;
    private const ODD_FLOOR = 1.70;
    private const MAX_ODD_CAP = 1.99;

    private const DEFAULT_SETTINGS = [
        'is_enabled' => true,
        'bankroll_gate_amount' => 500.00,
        'low_bet_threshold' => 3,
        'very_low_bet_threshold' => 1,
        'low_bet_boost' => 0.120,
        'very_low_bet_boost' => 0.220,
        'max_free_odd' => 1.99,
        'max_premium_odd' => 1.99,
        'min_house_margin_percent' => 30.00,
    ];

    public function getMergedSettings(int $modalidadeId): array
    {
        $stored = ModalidadeOddsSetting::query()
            ->where('modalidade_id', $modalidadeId)
            ->first();

        if (!$stored) {
            return self::DEFAULT_SETTINGS;
        }

        $db = $stored->only([
            'is_enabled',
            'bankroll_gate_amount',
            'low_bet_threshold',
            'very_low_bet_threshold',
            'low_bet_boost',
            'very_low_bet_boost',
            'max_free_odd',
            'max_premium_odd',
            'min_house_margin_percent',
        ]);

        return array_merge(self::DEFAULT_SETTINGS, $db);
    }

    public function getFinanceSnapshot(?int $rodeioId, int $modalidadeId): array
    {
        if (!Schema::hasTable('x1_payments') || !Schema::hasTable('x1_rooms')) {
            return [
                'paid_volume' => 0.0,
                'house_fee' => 0.0,
                'margin_percent' => 0.0,
                'paid_count' => 0,
                'avg_ticket' => 0.0,
            ];
        }

        $query = DB::table('x1_payments as p')
            ->join('x1_rooms as r', 'r.id', '=', 'p.x1_room_id')
            ->where('p.status', 'paid')
            ->where('r.modalidade_id', $modalidadeId);

        if ($rodeioId) {
            $query->where('r.rodeio_id', $rodeioId);
        }

        $totals = $query
            ->selectRaw('COALESCE(SUM(p.amount), 0) as paid_volume')
            ->selectRaw('COALESCE(SUM(p.amount * (p.fee_percent / 100)), 0) as house_fee')
            ->selectRaw('COUNT(*) as paid_count')
            ->first();

        $paidVolume = (float) ($totals->paid_volume ?? 0);
        $houseFee = (float) ($totals->house_fee ?? 0);
        $paidCount = (int) ($totals->paid_count ?? 0);
        $marginPercent = $paidVolume > 0 ? ($houseFee / $paidVolume) * 100 : 0.0;
        $avgTicket = $paidCount > 0 ? ($paidVolume / $paidCount) : 0.0;

        return [
            'paid_volume' => round($paidVolume, 2),
            'house_fee' => round($houseFee, 2),
            'margin_percent' => round($marginPercent, 2),
            'paid_count' => $paidCount,
            'avg_ticket' => round($avgTicket, 2),
        ];
    }

    public function getCompetitorBetCounts(?int $rodeioId, int $modalidadeId): array
    {
        if (!Schema::hasTable('x1_rooms')) {
            return [];
        }

        $counts = [];
        $hasParticipantsTable = Schema::hasTable('x1_participants');
        $hasParticipantCompetitor = $hasParticipantsTable && Schema::hasColumn('x1_participants', 'competitor_id');
        $hasParticipantPaymentStatus = $hasParticipantsTable && Schema::hasColumn('x1_participants', 'payment_status');

        // Fonte principal: participantes pagos (host + oponente) para refletir volume real de participação.
        if ($hasParticipantCompetitor) {
            $participantQuery = DB::table('x1_participants as p')
                ->join('x1_rooms as r', 'r.id', '=', 'p.x1_room_id')
                ->selectRaw('p.competitor_id as competitor_ref')
                ->selectRaw('COUNT(*) as total')
                ->where('r.modalidade_id', $modalidadeId)
                ->whereIn('r.status', self::ROOM_STATUSES_FOR_VOLUME)
                ->whereNotNull('p.competitor_id');

            if ($hasParticipantPaymentStatus) {
                $participantQuery->where('p.payment_status', 'paid');
            }

            if ($rodeioId) {
                $participantQuery->where('r.rodeio_id', $rodeioId);
            }

            $participantRows = $participantQuery
                ->groupBy('p.competitor_id')
                ->get();

            foreach ($participantRows as $row) {
                $competitorId = (int) ($row->competitor_ref ?? 0);
                if ($competitorId > 0) {
                    $counts[$competitorId] = (int) ($row->total ?? 0);
                }
            }
        }

        // Fallback legado: quartos antigos sem participant pago vinculado.
        if (!Schema::hasColumn('x1_rooms', 'competitor_id')) {
            return $counts;
        }

        $hasLegacyCompetitorColumn = Schema::hasColumn('x1_rooms', 'competitor_escolhido_criador');
        $competitorRefExpr = $hasLegacyCompetitorColumn
            ? 'COALESCE(r.competitor_id, r.competitor_escolhido_criador)'
            : 'r.competitor_id';

        $legacyQuery = DB::table('x1_rooms as r')
            ->selectRaw($competitorRefExpr . ' as competitor_ref')
            ->selectRaw('COUNT(*) as total')
            ->where('r.modalidade_id', $modalidadeId)
            ->whereIn('r.status', self::ROOM_STATUSES_FOR_VOLUME)
            ->where(function ($subQuery) use ($hasLegacyCompetitorColumn) {
                $subQuery->whereNotNull('r.competitor_id');
                if ($hasLegacyCompetitorColumn) {
                    $subQuery->orWhereNotNull('r.competitor_escolhido_criador');
                }
            });

        if ($rodeioId) {
            $legacyQuery->where('r.rodeio_id', $rodeioId);
        }

        if ($hasParticipantsTable) {
            $legacyQuery->whereNotExists(function ($subQuery) use ($hasParticipantPaymentStatus) {
                $subQuery->selectRaw('1')
                    ->from('x1_participants as p')
                    ->whereColumn('p.x1_room_id', 'r.id')
                    ->whereNotNull('p.competitor_id');

                if ($hasParticipantPaymentStatus) {
                    $subQuery->where('p.payment_status', 'paid');
                }
            });
        }

        $legacyRows = $legacyQuery
            ->groupBy('competitor_ref')
            ->get();

        foreach ($legacyRows as $row) {
            $competitorId = (int) ($row->competitor_ref ?? 0);
            if ($competitorId <= 0) {
                continue;
            }

            $counts[$competitorId] = (int) ($counts[$competitorId] ?? 0) + (int) ($row->total ?? 0);
        }

        return $counts;
    }

    public function getGroupBetCounts(?int $rodeioId, int $modalidadeId): array
    {
        if (!Schema::hasTable('x1_rooms')) {
            return [];
        }

        $counts = [];
        $hasParticipantsTable = Schema::hasTable('x1_participants');
        $hasParticipantGroup = $hasParticipantsTable && Schema::hasColumn('x1_participants', 'competitor_group_id');
        $hasParticipantPaymentStatus = $hasParticipantsTable && Schema::hasColumn('x1_participants', 'payment_status');

        if ($hasParticipantGroup) {
            $participantQuery = DB::table('x1_participants as p')
                ->join('x1_rooms as r', 'r.id', '=', 'p.x1_room_id')
                ->selectRaw('p.competitor_group_id as group_ref')
                ->selectRaw('COUNT(*) as total')
                ->where('r.modalidade_id', $modalidadeId)
                ->whereIn('r.status', self::ROOM_STATUSES_FOR_VOLUME)
                ->whereNotNull('p.competitor_group_id');

            if ($hasParticipantPaymentStatus) {
                $participantQuery->where('p.payment_status', 'paid');
            }

            if ($rodeioId) {
                $participantQuery->where('r.rodeio_id', $rodeioId);
            }

            $participantRows = $participantQuery
                ->groupBy('p.competitor_group_id')
                ->get();

            foreach ($participantRows as $row) {
                $groupId = (int) ($row->group_ref ?? 0);
                if ($groupId > 0) {
                    $counts[$groupId] = (int) ($row->total ?? 0);
                }
            }
        }

        if (!Schema::hasColumn('x1_rooms', 'competitor_group_id')) {
            return $counts;
        }

        $legacyQuery = DB::table('x1_rooms as r')
            ->selectRaw('r.competitor_group_id as group_ref')
            ->selectRaw('COUNT(*) as total')
            ->where('r.modalidade_id', $modalidadeId)
            ->whereIn('r.status', self::ROOM_STATUSES_FOR_VOLUME)
            ->whereNotNull('r.competitor_group_id');

        if ($rodeioId) {
            $legacyQuery->where('r.rodeio_id', $rodeioId);
        }

        if ($hasParticipantsTable) {
            $legacyQuery->whereNotExists(function ($subQuery) use ($hasParticipantPaymentStatus) {
                $subQuery->selectRaw('1')
                    ->from('x1_participants as p')
                    ->whereColumn('p.x1_room_id', 'r.id')
                    ->whereNotNull('p.competitor_group_id');

                if ($hasParticipantPaymentStatus) {
                    $subQuery->where('p.payment_status', 'paid');
                }
            });
        }

        $legacyRows = $legacyQuery
            ->groupBy('r.competitor_group_id')
            ->get();

        foreach ($legacyRows as $row) {
            $groupId = (int) ($row->group_ref ?? 0);
            if ($groupId <= 0) {
                continue;
            }

            $counts[$groupId] = (int) ($counts[$groupId] ?? 0) + (int) ($row->total ?? 0);
        }

        return $counts;
    }

    public function countLowVolumeCompetitors(?int $rodeioId, int $modalidadeId, int $threshold): int
    {
        if (!Schema::hasTable('competitor_modalidade')) {
            return 0;
        }

        $competitorIds = DB::table('competitor_modalidade')
            ->where('modalidade_id', $modalidadeId)
            ->pluck('competitor_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $counts = $this->getCompetitorBetCounts($rodeioId, $modalidadeId);

        return $competitorIds->filter(function (int $competitorId) use ($counts, $threshold) {
            return (int) ($counts[$competitorId] ?? 0) <= $threshold;
        })->count();
    }

    public function isBoostAvailable(array $settings, array $finance): bool
    {
        return (bool) ($this->calculateProfitAllocation($settings, $finance)['is_active'] ?? false);
    }

    public function buildOddsMap(Collection $competitors, ?int $rodeioId, ?int $modalidadeId, bool $isPremiumUser): array
    {
        if (!$modalidadeId || $competitors->isEmpty()) {
            return [
                'odds' => [],
                'settings' => self::DEFAULT_SETTINGS,
                'finance' => [
                    'paid_volume' => 0.0,
                    'house_fee' => 0.0,
                    'margin_percent' => 0.0,
                    'paid_count' => 0,
                    'avg_ticket' => 0.0,
                ],
                'boost_global' => false,
                'allocation' => [
                    'is_active' => false,
                    'is_gate_reached' => false,
                    'required_profit' => 0.0,
                    'excess_profit' => 0.0,
                    'boost_budget' => 0.0,
                    'profit_share_percent' => self::PROFIT_SHARE_PERCENT,
                ],
            ];
        }

        $modalidadeId = (int) $modalidadeId;
        $settings = $this->getMergedSettings($modalidadeId);
        $finance = $this->getFinanceSnapshot($rodeioId, $modalidadeId);
        $counts = $this->getCompetitorBetCounts($rodeioId, $modalidadeId);
        $allocation = $this->calculateProfitAllocation($settings, $finance);
        $configuredMaxFree = (float) ($settings['max_free_odd'] ?? self::DEFAULT_SETTINGS['max_free_odd']);
        $maxFree = min(self::MAX_ODD_CAP, $configuredMaxFree);
        $configuredMaxPremium = (float) ($settings['max_premium_odd'] ?? self::DEFAULT_SETTINGS['max_premium_odd']);
        $maxPremium = min(self::MAX_ODD_CAP, $configuredMaxPremium);

        $baseMap = [];

        foreach ($competitors as $competitor) {
            $competitorId = (int) ($competitor->id ?? 0);
            if ($competitorId <= 0) {
                continue;
            }

            $aproveitamento = (float) ($competitor->stats->aproveitamento ?? 0);
            $isNew = $competitor->created_at && $competitor->created_at->gt(now()->subDays(7));
            $x1Count = (int) ($counts[$competitorId] ?? 0);
            $nivelKey = $this->normalizeNivel((string) ($competitor->nivel ?? 'competidor'));

            // Seed determinístico diário por competidor (amplitude reduzida para não sobrepor pressão de demanda)
            $seed = crc32($competitorId . '-' . now()->format('Y-z'));
            $variation = (($seed % 1000) / 1000) * 0.05 - 0.03; // -0.03 até +0.02

            $perfBonus = 0.0;
            if ($aproveitamento >= 80) {
                $perfBonus = -0.03;
            } elseif ($aproveitamento >= 60) {
                $perfBonus = -0.01;
            } elseif ($aproveitamento <= 20 && $aproveitamento > 0) {
                $perfBonus = 0.02;
            }

            $novatoBonus = $isNew ? 0.02 : 0.0;
            $decrementPer3X1s = 0.025;
            $linearDemandPenalty = min(0.08, $x1Count * 0.004);
            $demandPenalty = min(0.22, (floor($x1Count / 3) * $decrementPer3X1s) + $linearDemandPenalty);

            $baseFree = 1.90;
            $baseFreeOdd = $baseFree + $variation + $perfBonus + $novatoBonus - $demandPenalty;

            // Premium mais convidativo: spread dinâmico maior quando a free estiver "apertada".
            // Ex.: free 1.80 -> premium tende a 1.93+ (respeitando teto premium).
            $premiumUplift = 0.12; // spread base mais agressivo para valorizar o Premium
            if ($x1Count >= 10) {
                $premiumUplift += 0.03;
            } elseif ($x1Count >= 5) {
                $premiumUplift += 0.02;
            }
            if ($baseFreeOdd < 1.85) {
                $premiumUplift += 0.04;
            }
            if ($baseFreeOdd < 1.80) {
                $premiumUplift += 0.03;
            }

            $basePremiumOdd = $baseFreeOdd + $premiumUplift;

            $baseMap[$competitorId] = [
                'x1_count' => $x1Count,
                'nivel_key' => $nivelKey,
                'base_free_odd' => (float) $baseFreeOdd,
                'base_premium_odd' => (float) $basePremiumOdd,
                'is_new' => $isNew,
            ];
        }

        $boostMap = $this->allocateBoosts(
            $baseMap,
            $settings,
            $finance,
            $allocation,
            $maxPremium
        );

        $odds = [];
        foreach ($baseMap as $competitorId => $baseData) {
            $boost = (float) ($boostMap[$competitorId]['boost'] ?? 0.0);
            $tier = $boostMap[$competitorId]['tier'] ?? null;

            $freeMultiplier = $this->clamp(
                ((float) $baseData['base_free_odd']) + $boost,
                self::ODD_FLOOR,
                $maxFree
            );
            $premiumMultiplier = $this->clamp(
                ((float) $baseData['base_premium_odd']) + $boost,
                self::ODD_FLOOR,
                $maxPremium
            );
            $displayMultiplier = $isPremiumUser ? $premiumMultiplier : $freeMultiplier;

            $odds[$competitorId] = [
                'x1_count' => (int) ($baseData['x1_count'] ?? 0),
                'free_multiplier' => round($freeMultiplier, 2),
                'premium_multiplier' => round($premiumMultiplier, 2),
                'display_multiplier' => round($displayMultiplier, 2),
                'boost_applied' => $boost > 0,
                'boost_amount' => round($boost, 3),
                'boost_tier' => $tier,
                'nivel_key' => (string) ($baseData['nivel_key'] ?? 'competidor'),
                'is_new' => (bool) ($baseData['is_new'] ?? false),
            ];
        }

        return [
            'odds' => $odds,
            'settings' => $settings,
            'finance' => $finance,
            'boost_global' => (bool) ($allocation['is_active'] ?? false),
            'allocation' => $allocation,
        ];
    }

    public function buildGroupOddsMap(Collection $groups, ?int $rodeioId, ?int $modalidadeId, bool $isPremiumUser): array
    {
        if (!$modalidadeId || $groups->isEmpty()) {
            return [
                'odds' => [],
                'settings' => self::DEFAULT_SETTINGS,
                'finance' => [
                    'paid_volume' => 0.0,
                    'house_fee' => 0.0,
                    'margin_percent' => 0.0,
                    'paid_count' => 0,
                    'avg_ticket' => 0.0,
                ],
                'boost_global' => false,
                'allocation' => [
                    'is_active' => false,
                    'is_gate_reached' => false,
                    'required_profit' => 0.0,
                    'excess_profit' => 0.0,
                    'boost_budget' => 0.0,
                    'profit_share_percent' => self::PROFIT_SHARE_PERCENT,
                ],
            ];
        }

        $modalidadeId = (int) $modalidadeId;
        $settings = $this->getMergedSettings($modalidadeId);
        $finance = $this->getFinanceSnapshot($rodeioId, $modalidadeId);
        $counts = $this->getGroupBetCounts($rodeioId, $modalidadeId);
        $allocation = $this->calculateProfitAllocation($settings, $finance);
        $configuredMaxFree = (float) ($settings['max_free_odd'] ?? self::DEFAULT_SETTINGS['max_free_odd']);
        $maxFree = min(self::MAX_ODD_CAP, $configuredMaxFree);
        $configuredMaxPremium = (float) ($settings['max_premium_odd'] ?? self::DEFAULT_SETTINGS['max_premium_odd']);
        $maxPremium = min(self::MAX_ODD_CAP, $configuredMaxPremium);

        $baseMap = [];
        $freshThreshold = now()->subDays(7);
        $daySeed = now()->format('Y-z');

        foreach ($groups as $group) {
            $groupId = (int) ($group->id ?? 0);
            if ($groupId <= 0) {
                continue;
            }

            $members = collect($group->members ?? []);
            if ($members->isEmpty()) {
                continue;
            }

            $aproveitamento = (float) $members->avg(function ($member) {
                return (float) ($member->stats->aproveitamento ?? 0);
            });
            $isNew = $members->contains(function ($member) use ($freshThreshold) {
                return $member->created_at && $member->created_at->gt($freshThreshold);
            });
            $x1Count = (int) ($counts[$groupId] ?? 0);
            $nivelKey = $members
                ->map(fn ($member) => $this->normalizeNivel((string) ($member->nivel ?? 'competidor')))
                ->sortByDesc(fn (string $nivel) => $this->levelPriority($nivel))
                ->first() ?? 'competidor';

            $seed = crc32('group-' . $groupId . '-' . $daySeed);
            $variation = (($seed % 1000) / 1000) * 0.05 - 0.03;

            $perfBonus = 0.0;
            if ($aproveitamento >= 80) {
                $perfBonus = -0.03;
            } elseif ($aproveitamento >= 60) {
                $perfBonus = -0.01;
            } elseif ($aproveitamento <= 20 && $aproveitamento > 0) {
                $perfBonus = 0.02;
            }

            $novatoBonus = $isNew ? 0.02 : 0.0;
            $decrementPer3X1s = 0.025;
            $linearDemandPenalty = min(0.08, $x1Count * 0.004);
            $demandPenalty = min(0.22, (floor($x1Count / 3) * $decrementPer3X1s) + $linearDemandPenalty);

            $baseFree = 1.90;
            $baseFreeOdd = $baseFree + $variation + $perfBonus + $novatoBonus - $demandPenalty;

            $premiumUplift = 0.12;
            if ($x1Count >= 10) {
                $premiumUplift += 0.03;
            } elseif ($x1Count >= 5) {
                $premiumUplift += 0.02;
            }
            if ($baseFreeOdd < 1.85) {
                $premiumUplift += 0.04;
            }
            if ($baseFreeOdd < 1.80) {
                $premiumUplift += 0.03;
            }

            $baseMap[$groupId] = [
                'x1_count' => $x1Count,
                'nivel_key' => $nivelKey,
                'base_free_odd' => (float) $baseFreeOdd,
                'base_premium_odd' => (float) ($baseFreeOdd + $premiumUplift),
                'is_new' => $isNew,
            ];
        }

        $boostMap = $this->allocateBoosts(
            $baseMap,
            $settings,
            $finance,
            $allocation,
            $maxPremium
        );

        $odds = [];
        foreach ($baseMap as $groupId => $baseData) {
            $boost = (float) ($boostMap[$groupId]['boost'] ?? 0.0);
            $tier = $boostMap[$groupId]['tier'] ?? null;

            $freeMultiplier = $this->clamp(
                ((float) $baseData['base_free_odd']) + $boost,
                self::ODD_FLOOR,
                $maxFree
            );
            $premiumMultiplier = $this->clamp(
                ((float) $baseData['base_premium_odd']) + $boost,
                self::ODD_FLOOR,
                $maxPremium
            );
            $displayMultiplier = $isPremiumUser ? $premiumMultiplier : $freeMultiplier;

            $odds[$groupId] = [
                'x1_count' => (int) ($baseData['x1_count'] ?? 0),
                'free_multiplier' => round($freeMultiplier, 2),
                'premium_multiplier' => round($premiumMultiplier, 2),
                'display_multiplier' => round($displayMultiplier, 2),
                'boost_applied' => $boost > 0,
                'boost_amount' => round($boost, 3),
                'boost_tier' => $tier,
                'nivel_key' => (string) ($baseData['nivel_key'] ?? 'competidor'),
                'is_new' => (bool) ($baseData['is_new'] ?? false),
            ];
        }

        return [
            'odds' => $odds,
            'settings' => $settings,
            'finance' => $finance,
            'boost_global' => (bool) ($allocation['is_active'] ?? false),
            'allocation' => $allocation,
        ];
    }

    private function calculateProfitAllocation(array $settings, array $finance): array
    {
        $paidVolume = (float) ($finance['paid_volume'] ?? 0);
        $houseFee = (float) ($finance['house_fee'] ?? 0);
        $gateMarginPercent = max(30.0, (float) ($settings['min_house_margin_percent'] ?? 30.0));
        $requiredProfit = $paidVolume * ($gateMarginPercent / 100);
        $excessProfit = max(0, $houseFee - $requiredProfit);
        $boostBudget = $excessProfit * (self::PROFIT_SHARE_PERCENT / 100);

        $isGateReached = $paidVolume >= (float) ($settings['bankroll_gate_amount'] ?? 0)
            && $houseFee >= $requiredProfit;

        $isActive = (bool) ($settings['is_enabled'] ?? false)
            && $isGateReached
            && $boostBudget > 0;

        return [
            'is_active' => $isActive,
            'is_gate_reached' => $isGateReached,
            'required_profit' => round($requiredProfit, 2),
            'excess_profit' => round($excessProfit, 2),
            'boost_budget' => round($boostBudget, 2),
            'profit_share_percent' => self::PROFIT_SHARE_PERCENT,
        ];
    }

    /**
     * Distribui orçamento de boost:
     * - Primeiro competidores com 0 participação
     * - Dentro da mesma faixa de participação, prioridade de nível:
     *   favorito > elite > ascendente > competidor
     * - Usa 30% do lucro excedente para não comprometer margem base.
     */
    private function allocateBoosts(
        array $baseMap,
        array $settings,
        array $finance,
        array $allocation,
        float $maxPremiumOdd
    ): array {
        if (!(bool) ($allocation['is_active'] ?? false)) {
            return [];
        }

        $lowThreshold = (int) ($settings['low_bet_threshold'] ?? 3);
        $veryLowThreshold = (int) ($settings['very_low_bet_threshold'] ?? 1);

        $candidates = [];
        foreach ($baseMap as $competitorId => $baseData) {
            $x1Count = (int) ($baseData['x1_count'] ?? 0);
            if ($x1Count > $lowThreshold) {
                continue;
            }

            $nivelKey = (string) ($baseData['nivel_key'] ?? 'competidor');
            $effectiveBasePremium = max(self::ODD_FLOOR, (float) ($baseData['base_premium_odd'] ?? self::ODD_FLOOR));
            // As odds continuam clampadas em 1.99x no final; o boost precisa usar o
            // espaço do Premium para não "prender" o premium ao delta fixo de +0.03.
            $headroom = max(0.0, $maxPremiumOdd - $effectiveBasePremium);

            if ($headroom <= 0) {
                continue;
            }

            $candidates[] = [
                'competitor_id' => (int) $competitorId,
                'x1_count' => $x1Count,
                'nivel_key' => $nivelKey,
                'headroom' => $headroom,
            ];
        }

        if (empty($candidates)) {
            return [];
        }

        usort($candidates, function (array $a, array $b) {
            // Menos participações primeiro
            if ($a['x1_count'] !== $b['x1_count']) {
                return $a['x1_count'] <=> $b['x1_count'];
            }

            // Prioridade por nível na mesma faixa
            $prioA = $this->levelPriority((string) $a['nivel_key']);
            $prioB = $this->levelPriority((string) $b['nivel_key']);
            if ($prioA !== $prioB) {
                return $prioB <=> $prioA;
            }

            return $a['competitor_id'] <=> $b['competitor_id'];
        });

        $remainingBudget = (float) ($allocation['boost_budget'] ?? 0);
        $avgTicket = max(20.0, (float) ($finance['avg_ticket'] ?? 0));
        $costPerOneOdd = $avgTicket * 10.0;
        $boostMap = [];

        foreach ($candidates as $candidate) {
            if ($remainingBudget <= 0) {
                break;
            }

            $x1Count = (int) $candidate['x1_count'];
            $nivelKey = (string) $candidate['nivel_key'];
            $headroom = (float) $candidate['headroom'];

            $baseCap = $this->baseCapByDemand($x1Count, $veryLowThreshold, $lowThreshold);
            $levelFactor = $this->levelBoostFactor($nivelKey);
            $maxBoostForCompetitor = $baseCap * $levelFactor;
            $affordableBoost = $remainingBudget / $costPerOneOdd;

            $boost = min($headroom, $maxBoostForCompetitor, $affordableBoost);
            if ($boost < 0.005) {
                continue;
            }

            $boost = round($boost, 3);
            $budgetUsed = $boost * $costPerOneOdd;
            $remainingBudget = max(0.0, $remainingBudget - $budgetUsed);

            $boostMap[(int) $candidate['competitor_id']] = [
                'boost' => $boost,
                'tier' => $this->tierByCount($x1Count, $veryLowThreshold, $lowThreshold),
                'budget_used' => round($budgetUsed, 2),
            ];
        }

        return $boostMap;
    }

    private function normalizeNivel(string $nivel): string
    {
        $normalized = strtolower(trim($nivel));
        $normalized = strtr($normalized, [
            'á' => 'a',
            'ã' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
        ]);

        if ($normalized === 'legado') {
            $normalized = 'ascendente';
        }
        if ($normalized === 'presilha') {
            $normalized = 'competidor';
        }
        if (!in_array($normalized, ['favorito', 'elite', 'ascendente', 'competidor'], true)) {
            $normalized = 'competidor';
        }

        return $normalized;
    }

    private function levelPriority(string $nivelKey): int
    {
        return match ($nivelKey) {
            'favorito' => 4,
            'elite' => 3,
            'ascendente' => 2,
            default => 1, // competidor
        };
    }

    private function levelBoostFactor(string $nivelKey): float
    {
        return match ($nivelKey) {
            'favorito' => 1.20,
            'elite' => 1.00,
            'ascendente' => 0.85,
            default => 0.75, // competidor
        };
    }

    private function baseCapByDemand(int $x1Count, int $veryLowThreshold, int $lowThreshold): float
    {
        if ($x1Count <= 0) {
            return 0.60;
        }
        if ($x1Count <= $veryLowThreshold) {
            return 0.45;
        }
        if ($x1Count <= $lowThreshold) {
            return 0.28;
        }

        return 0.0;
    }

    private function tierByCount(int $x1Count, int $veryLowThreshold, int $lowThreshold): ?string
    {
        if ($x1Count <= 0) {
            return 'zero';
        }
        if ($x1Count <= $veryLowThreshold) {
            return 'very_low';
        }
        if ($x1Count <= $lowThreshold) {
            return 'low';
        }

        return null;
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}
