<?php

namespace App\Services;

use App\Models\Modalidade;
use App\Models\CompetitorContextStat;
use App\Models\RankingSnapshot;
use Illuminate\Support\Facades\Schema;

class RankingSnapshotService
{
    /**
     * Gera um payload de ranking a partir dos dados persistidos (competitors + competitor_stats)
     * para uma modalidade, opcionalmente filtrado por divisão (para finais).
     */
    public function buildModalidadeRankingPayload(int $modalidadeId, ?int $rodeioId = null, ?string $divisao = null): array
    {
        $modalidade = Modalidade::query()
            ->with(['competitors'])
            ->findOrFail($modalidadeId);

        $competitors = $modalidade->competitors;
        $scoresByCompetitorId = [];

        if ($rodeioId && Schema::hasTable('competitor_stats')) {
            $query = CompetitorContextStat::query()
                ->where('rodeio_id', (int) $rodeioId)
                ->where('modalidade_id', $modalidadeId)
                ->whereIn('competitor_id', $competitors->pluck('id'));
            
            // Filtrar por divisão se fornecida (finais com divisões)
            if ($divisao !== null) {
                $query->where('divisao', trim((string) $divisao));
            } else {
                // Se não fornecida, assumir classificatória (divisão vazia)
                $query->where('divisao', '');
            }
            
            $scoresByCompetitorId = $query
                ->pluck('pontuacao_total', 'competitor_id')
                ->map(fn($v) => (int) $v)
                ->all();
        }

        $ranking = $competitors
            ->map(function ($competitor) use ($scoresByCompetitorId) {
                $score = (int) ($scoresByCompetitorId[$competitor->id] ?? 0);

                return [
                    'competitor_id' => $competitor->id,
                    'nome' => $competitor->nome,
                    'foto_url' => $competitor->foto_url ?? null,
                    'score' => $score,
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->all();

        $statistics = [
            'total_competitors_ranked' => count($ranking),
            'top_score' => $ranking[0]['score'] ?? 0,
            'average_score' => $this->calculateAverageScore($ranking),
        ];

        return [
            'modalidade_id' => $modalidadeId,
            'rodeio_id' => $rodeioId,
            'divisao' => $divisao ?? '',
            'ranking' => $ranking,
            'statistics' => $statistics,
        ];
    }

    public function storeSnapshot(int $modalidadeId, ?int $rodeioId, array $payload): ?RankingSnapshot
    {
        if (!Schema::hasTable('ranking_snapshots')) {
            return null;
        }

        return RankingSnapshot::create([
            'modalidade_id' => $modalidadeId,
            'rodeio_id' => $rodeioId,
            'payload' => $payload,
            'generated_at' => now(),
        ]);
    }

    private function calculateAverageScore(array $ranking): float
    {
        if ($ranking === []) {
            return 0;
        }

        $total = 0;
        $count = 0;

        foreach ($ranking as $row) {
            if (isset($row['score'])) {
                $total += (int) $row['score'];
                $count++;
            }
        }

        return $count > 0 ? round($total / $count, 2) : 0;
    }
}
