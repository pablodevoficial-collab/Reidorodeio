<?php

namespace App\Services;

use App\Models\Competitor;
use App\Models\CompetitorContextStat;
use App\Models\CompetitorScoringLog;
use App\Models\Rodeio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Serviço centralizado para gerenciar estatísticas de competidores
 */
class CompetitorStatisticsService
{
    /**
     * Atualizar estatísticas contextuais (por rodeio + modalidade + divisão)
     */
    public function updateStats(
        int $competitorId,
        int $rodeioId,
        int $modalidadeId,
        string $divisao,
        string $tipoFase,
        string $action,
        int $points
    ): CompetitorContextStat {
        $stats = CompetitorContextStat::query()->firstOrCreate(
            [
                'competitor_id' => $competitorId,
                'rodeio_id' => $rodeioId,
                'modalidade_id' => $modalidadeId,
                'divisao' => $divisao,
            ],
            [
                'tipo_fase' => $tipoFase,
                'pontuacao_total' => 0,
                'last_points' => 0,
                'is_finalized' => false,
            ]
        );

        // Atualizar tipo_fase se mudou
        if ($stats->tipo_fase !== $tipoFase) {
            $stats->tipo_fase = $tipoFase;
        }

        // Mapear ações para contadores
        $actionToCounter = $this->getActionCounterMap();

        // Atualizar contadores específicos
        if ($action === 'custom') {
            $stats->increment('count_custom');
            $stats->increment('points_custom_total', $points);
        } elseif (isset($actionToCounter[$action])) {
            $stats->increment($actionToCounter[$action]);
        } else {
            $counts = is_array($stats->action_counts) ? $stats->action_counts : [];
            $counts[$action] = (int) ($counts[$action] ?? 0) + 1;
            $stats->action_counts = $counts;
        }

        // Incrementar contador de negativos se for ação negativa
        if ($this->isNegativeAction($action)) {
            $stats->increment('count_negativas_total');
        }

        // Atualizar pontuação total
        $stats->increment('pontuacao_total', $points);
        $stats->last_points = $points;
        $stats->last_updated_at = now();
        $stats->save();

        return $stats;
    }

    /**
     * Reverter última pontuação
     */
    public function revertStats(
        int $competitorId,
        int $rodeioId,
        int $modalidadeId,
        string $divisao,
        string $action,
        int $points
    ): ?CompetitorContextStat {
        $stats = CompetitorContextStat::query()->where([
            'competitor_id' => $competitorId,
            'rodeio_id' => $rodeioId,
            'modalidade_id' => $modalidadeId,
            'divisao' => $divisao,
        ])->first();

        if (!$stats) {
            return null;
        }

        $actionToCounter = $this->getActionCounterMap();

        $updates = [
            'pontuacao_total' => DB::raw('COALESCE(pontuacao_total,0) - ' . ((int) $points)),
            'last_points' => -((int) $points),
            'last_updated_at' => now(),
        ];

        if ($action === 'custom') {
            $updates['count_custom'] = DB::raw('GREATEST(COALESCE(count_custom,0) - 1, 0)');
            $updates['points_custom_total'] = DB::raw('COALESCE(points_custom_total,0) - ' . ((int) $points));
        } elseif (isset($actionToCounter[$action])) {
            $col = $actionToCounter[$action];
            $updates[$col] = DB::raw("GREATEST(COALESCE({$col},0) - 1, 0)");
        } else {
            $counts = is_array($stats->action_counts) ? $stats->action_counts : [];
            if (isset($counts[$action]) && (int) $counts[$action] > 0) {
                $counts[$action] = (int) $counts[$action] - 1;
                if ((int) $counts[$action] <= 0) {
                    unset($counts[$action]);
                }
                $stats->action_counts = $counts;
            }
        }

        if ($this->isNegativeAction($action)) {
            $updates['count_negativas_total'] = DB::raw('GREATEST(COALESCE(count_negativas_total,0) - 1, 0)');
        }

        CompetitorContextStat::query()->whereKey($stats->getKey())->update($updates);
        $stats->refresh();
        $stats->save();

        return $stats;
    }

    /**
     * Finalizar estatísticas de um competidor (marcar como finalizadas)
     */
    public function finalizeStats(
        int $competitorId,
        int $rodeioId,
        int $modalidadeId,
        string $divisao,
        string $tipoFase
    ): bool {
        return CompetitorContextStat::query()
            ->where('competitor_id', $competitorId)
            ->where('rodeio_id', $rodeioId)
            ->where('modalidade_id', $modalidadeId)
            ->where('divisao', $divisao)
            ->where('tipo_fase', $tipoFase)
            ->update([
                'is_finalized' => true,
                'last_updated_at' => now(),
            ]) > 0;
    }

    /**
     * Finalizar classificatória inteira (marcar todos competidores)
     */
    public function finalizeClassificatoria(int $rodeioId, int $modalidadeId): int
    {
        return CompetitorContextStat::query()
            ->where('rodeio_id', $rodeioId)
            ->where('modalidade_id', $modalidadeId)
            ->where('tipo_fase', 'classificatoria')
            ->where('is_finalized', false)
            ->update([
                'is_finalized' => true,
                'last_updated_at' => now(),
            ]);
    }

    /**
     * Finalizar divisão (marcar todos competidores de uma divisão)
     */
    public function finalizeDivisao(int $rodeioId, int $modalidadeId, string $divisao): int
    {
        return CompetitorContextStat::query()
            ->where('rodeio_id', $rodeioId)
            ->where('modalidade_id', $modalidadeId)
            ->where('divisao', $divisao)
            ->where('tipo_fase', 'final')
            ->where('is_finalized', false)
            ->update([
                'is_finalized' => true,
                'last_updated_at' => now(),
            ]);
    }

    /**
     * Obter estatísticas de um competidor
     */
    public function getStats(
        int $competitorId,
        int $rodeioId,
        int $modalidadeId,
        ?string $divisao = null
    ): ?CompetitorContextStat {
        return CompetitorContextStat::query()
            ->where('competitor_id', $competitorId)
            ->where('rodeio_id', $rodeioId)
            ->where('modalidade_id', $modalidadeId)
            ->where('divisao', $divisao ?? '')
            ->first();
    }

    /**
     * Obter todas as estatísticas da classificatória
     */
    public function getClassificatoriaStats(int $rodeioId, int $modalidadeId): \Illuminate\Support\Collection
    {
        return CompetitorContextStat::query()
            ->where('rodeio_id', $rodeioId)
            ->where('modalidade_id', $modalidadeId)
            ->where('tipo_fase', 'classificatoria')
            ->orderByDesc('pontuacao_total')
            ->get();
    }

    /**
     * Obter todas as estatísticas de uma divisão
     */
    public function getFinalStats(int $rodeioId, int $modalidadeId, string $divisao): \Illuminate\Support\Collection
    {
        return CompetitorContextStat::query()
            ->where('rodeio_id', $rodeioId)
            ->where('modalidade_id', $modalidadeId)
            ->where('divisao', $divisao)
            ->where('tipo_fase', 'final')
            ->orderByDesc('pontuacao_total')
            ->get();
    }

    /**
     * Mapear ações para contadores
     */
    private function getActionCounterMap(): array
    {
        return [
            'boa' => 'count_boa',
            'errou_pescoco' => 'count_errou_pescoco',
            'errou_pata' => 'count_errou_pata',
            'errou_top' => 'count_errou_top',
            'dobrada' => 'count_dobrada',
            'cabresteou' => 'count_cabresteou',
            'duas_voltas' => 'count_duas_voltas',
            'limpou_garupa' => 'count_limpou_garupa',
            'garupa' => 'count_garupa_neg',
            'cola' => 'count_cola',
            'cola_neg' => 'count_cola_neg',
            'cupim' => 'count_cupim',
            'top' => 'count_top',
            'pescou' => 'count_pescou',
            'uma_aspa' => 'count_uma_aspa',
            'por_cima' => 'count_por_cima',
            'limpou_cupim_longe' => 'count_limpou_cupim_longe',
            'pescou_uma_aspa' => 'count_pescou_uma_aspa',
            'limpou_top' => 'count_limpou_top',
            'limpou_top_mao' => 'count_limpou_top_mao',
            'boi_tirou' => 'count_boi_tirou',
            'boi_pulou' => 'count_boi_pulou',
            'queimou_raia' => 'count_queimou_raia',
            'caiu_do_cavalo' => 'count_caiu_do_cavalo',
            'saiu_enrolado' => 'count_saiu_enrolado',
        ];
    }

    /**
     * Verificar se ação é negativa
     */
    private function isNegativeAction(string $action): bool
    {
        $negativeActions = [
            'errou_pescoco',
            'errou_pata',
            'errou_top',
            'dobrada',
            'cabresteou',
            'duas_voltas',
            'garupa',
            'cola_neg',
            'uma_aspa',
            'por_cima',
            'boi_tirou',
            'boi_pulou',
            'queimou_raia',
            'caiu_do_cavalo',
            'saiu_enrolado',
        ];

        return in_array($action, $negativeActions, true);
    }
}
