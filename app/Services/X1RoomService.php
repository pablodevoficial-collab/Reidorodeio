<?php

namespace App\Services;

use App\Models\Modalidade;
use App\Models\Competitor;
use App\Models\ModalidadeCompetitorGroup;
use App\Models\Rodeio;
use App\Models\User;
use App\Models\X1RoomInstance;

class X1RoomService
{
    public function resolveFeePercent(User $user, float $entryAmount): float
    {
        $isPremium = $user->isPremium();
        
        // ✅ NOVA LÓGICA: Salas até R$ 1.000: 10% normal / 7% premium
        if ($entryAmount <= 1000) {
            return $isPremium ? 7.0 : 10.0;
        }
        
        // ✅ Salas acima de R$ 1.000: 15% normal / 10% premium
        return $isPremium ? 10.0 : 15.0;
    }

    public function calculatePrizeTotal(float $entryAmount, float $feePercent): float
    {
        $total = $entryAmount * 2;
        $fee = $total * ($feePercent / 100);
        return round($total - $fee, 2);
    }

    public function validateRodeioModalidadeCompetitor(?int $rodeioId, ?int $modalidadeId, ?int $competitorId, ?int $competitorGroupId = null): void
    {
        if ($rodeioId) {
            Rodeio::whereKey($rodeioId)->firstOrFail();
        }

        $modalidade = null;
        if ($modalidadeId) {
            $modalidade = Modalidade::whereKey($modalidadeId)->firstOrFail();
            if ($rodeioId && $modalidade->rodeio_id && $modalidade->rodeio_id !== $rodeioId) {
                abort(422, 'Modalidade não pertence ao rodeio selecionado.');
            }
        }

        $teamSize = (int) ($modalidade?->tamanho_equipe ?? 1);
        if ($teamSize > 1) {
            if (!$competitorGroupId) {
                abort(422, 'Selecione um grupo válido para essa modalidade.');
            }

            $group = ModalidadeCompetitorGroup::whereKey($competitorGroupId)->firstOrFail();
            if ($modalidadeId && (int) $group->modalidade_id !== (int) $modalidadeId) {
                abort(422, 'Grupo não pertence à modalidade selecionada.');
            }

            return;
        }

        if ($competitorId) {
            $competitor = Competitor::whereKey($competitorId)->firstOrFail();
            if ($modalidadeId) {
                $isLinked = $competitor->modalidades()->where('modalidades.id', $modalidadeId)->exists();
                if (!$isLinked) {
                    abort(422, 'Competidor não pertence à modalidade selecionada.');
                }
            }
        } elseif ($modalidadeId) {
            abort(422, 'Selecione um competidor válido para essa modalidade.');
        }
    }

    /**
     * Busca salas X1 compatíveis aguardando oponente.
     * 
     * @param float $valor Valor da participação
     * @param int|null $rodeioId ID do rodeio
     * @param int|null $modalidadeId ID da modalidade
     * @param string|null $divisao Divisão (se houver)
     * @param int|null $excludeCompetitorId Excluir salas deste competidor
     * @param int|null $excludeCompetitorGroupId Excluir salas deste grupo
     * @return \Illuminate\Support\Collection
     */
    public function findCompatibleRooms(
        float $valor,
        ?int $rodeioId = null,
        ?int $modalidadeId = null,
        ?string $divisao = null,
        ?int $excludeCompetitorId = null,
        ?int $excludeCompetitorGroupId = null
    ) {
        // Usamos a nova tabela de instâncias de sala (x1_room_instances)
        $query = \App\Models\X1RoomInstance::query()
            ->where('valor_entrada', $valor)
            ->where('status', 'open') // somente salas realmente abertas
            ->where('is_private', false)
            ->whereNull('closed_at')
            ->whereHas('participants', function ($q) {
                $q->where('slot', 1)->where('is_host', true);
            }); // garantir que host já existe

        if ($rodeioId) {
            $query->where('rodeio_id', $rodeioId);
        }

        if ($modalidadeId) {
            $query->where('modalidade_id', $modalidadeId);
        }

        if ($divisao) {
            $query->where('divisao', $divisao);
        }

        // Excluir salas do mesmo competidor
        if ($excludeCompetitorId) {
            $query->where('competitor_id', '!=', $excludeCompetitorId);
        }

        // Excluir salas do mesmo grupo
        if ($excludeCompetitorGroupId) {
            $query->where('competitor_group_id', '!=', $excludeCompetitorGroupId);
        }

        return $query->with(['competitor', 'competitorGroup', 'host'])
            ->orderBy('created_at', 'asc') // FIFO: mais antigas primeiro
            ->get();
    }

    public function findCustomEntryRooms(
        float $minValor,
        ?int $rodeioId = null,
        ?int $modalidadeId = null,
        ?string $divisao = null,
        ?int $excludeCompetitorId = null,
        ?int $excludeCompetitorGroupId = null,
        int $limit = 4
    ) {
        $query = X1RoomInstance::query()
            ->where('valor_entrada', '>', $minValor)
            ->where('status', 'open')
            ->where('is_private', false)
            ->whereNull('closed_at')
            ->whereHas('participants', function ($q) {
                $q->where('slot', 1)->where('is_host', true);
            });

        if ($rodeioId) {
            $query->where('rodeio_id', $rodeioId);
        }

        if ($modalidadeId) {
            $query->where('modalidade_id', $modalidadeId);
        }

        if ($divisao) {
            $query->where('divisao', $divisao);
        }

        if ($excludeCompetitorId) {
            $query->where('competitor_id', '!=', $excludeCompetitorId);
        }

        if ($excludeCompetitorGroupId) {
            $query->where('competitor_group_id', '!=', $excludeCompetitorGroupId);
        }

        return $query->with(['competitor', 'competitorGroup', 'host'])
            ->orderBy('valor_entrada', 'asc')
            ->orderBy('created_at', 'asc')
            ->limit(max(1, $limit))
            ->get();
    }

    /**
     * Retorna detalhes formatados de uma sala para exibição.
     * 
     * @param \App\Models\X1Room $room
     * @return array
     */
    public function getRoomDetails(\App\Models\X1RoomInstance $room): array
    {
        $hostUser = $room->host;
        $feePercent = (float) ($room->fee_percent ?? 0);
        if ($feePercent <= 0 || $feePercent > 20) {
            $feePercent = $hostUser ? $this->resolveFeePercent($hostUser, (float) $room->valor_entrada) : 10.0;
        }
        $multiplier = 2 * (1 - ($feePercent / 100));
        $expectedPrize = $this->calculatePrizeTotal((float) $room->valor_entrada, $feePercent);
        $storedPrize = $room->prize_total !== null ? (float) $room->prize_total : null;
        $prizeTotal = ($storedPrize === null || abs($storedPrize - $expectedPrize) > 0.5)
            ? $expectedPrize
            : $storedPrize;

        return [
            'id' => $room->id,
            'valor_entrada' => $room->valor_entrada,
            'valor_entrada_formatted' => 'R$ ' . number_format($room->valor_entrada, 2, ',', '.'),
            'competitor_name' => $room->competitor?->nome ?? $room->competitorGroup?->nome ?? 'N/A',
            'competitor_photo' => $room->competitor?->foto_url ?? null,
            'fee_percent' => $feePercent,
            'multiplier' => round($multiplier, 2),
            'multiplier_formatted' => number_format($multiplier, 1, ',', '.') . 'x',
            'prize_total' => $prizeTotal,
            'prize_total_formatted' => 'R$ ' . number_format($prizeTotal, 2, ',', '.'),
            'waiting_time' => $room->created_at->diffForHumans(),
            'host_is_premium' => $hostUser ? $hostUser->isPremium() : false,
            'created_at' => $room->created_at,
        ];
    }
}
