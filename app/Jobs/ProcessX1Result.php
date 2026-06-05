<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\X1RoomInstance;
use App\Models\X1Result;
use App\Models\X1Participant;
use App\Models\User;
use App\Models\Transaction;
use App\Services\X1StatsService;
use Illuminate\Support\Facades\Log;

class ProcessX1Result implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public X1RoomInstance $room;

    public function __construct(X1RoomInstance $room)
    {
        $this->room = $room;
    }

    public function handle(): void
    {
        $participants = $this->room->participants()
            ->with(['user', 'competitor', 'competitorGroup'])
            ->where('payment_status', 'paid')
            ->get();

        if ($participants->count() < 2) {
            Log::warning('ProcessX1Result: Sala sem 2 participantes pagos', [
                'room_id' => $this->room->id,
                'participants_count' => $participants->count(),
            ]);
            return;
        }

        // Determinar vencedor baseado nos resultados dos competidores
        // Se houver empate (pontuações iguais), retorna null para processar reembolso com taxa
        $winnerId = $this->determineWinner($participants);
        
        // --- DRAW / REFUND LOGIC ---
        if ($winnerId === null) {
            Log::info('ProcessX1Result: Empate detectado. Iniciando reembolso com taxa de 1%.', [
                'room_id' => $this->room->id,
            ]);

            // Taxa de 1% sobre o valor da entrada
            $feePercentage = 0.01;
            $entryAmount = $this->room->valor_entrada;
            $feeAmount = $entryAmount * $feePercentage;
            $refundAmount = $entryAmount - $feeAmount;

            foreach ($participants as $participant) {
                $user = $participant->user;
                
                // Credit to receivable_balance instead of main balance
                $user->receivable_balance += $refundAmount;
                $user->save();

                Transaction::create([
                    'user_id' => $user->id,
                    'amount' => $refundAmount,
                    'charge' => $feeAmount,
                    'post_balance' => $user->receivable_balance, // Tracking receivable balance
                    'trx_type' => '+',
                    'details' => 'Reembolso X1 (Empate) - Taxa 1% descontada - Creditado em Valores a Receber',
                    'trx' => getTrx(),
                    'remark' => 'x1_refund_draw_receivable', // Distinct remark
                ]);
                
                // Update payment status
                \App\Models\X1Payment::where('x1_room_id', $this->room->id)
                    ->where('user_id', $user->id)
                    ->update(['status' => 'refunded_receivable']);
            }

            // Record Draw Stats
            if ($participants->count() >= 2) {
                try {
                    $p1 = $participants->first()->user_id;
                    $p2 = $participants->skip(1)->first()->user_id;
                    app(X1StatsService::class)->recordDraw($this->room, $p1, $p2);
                } catch (\Exception $e) {
                     Log::error("ProcessX1Result: Erro ao registrar stats de empate: " . $e->getMessage());
                }
            }

            // Create Result Record for Draw
            X1Result::create([
                'x1_room_id' => $this->room->id,
                'winner_user_id' => null,
                'payload' => [
                    'is_draw' => true,
                    'valor_entrada' => $this->room->valor_entrada,
                    'fee_deducted' => $feeAmount,
                    'refund_amount' => $refundAmount,
                    'modalidade_id' => $this->room->modalidade_id,
                    'processed_by' => 'job',
                ],
                'processed_at' => now(),
            ]);

            // ✅ Atualizar status da sala para finalizada (Empate)
            $this->room->update([
                'status' => 'finished',
                'finished_at' => now(),
            ]);

            return;
        }

        $loserId = $participants->where('user_id', '!=', $winnerId)->first()?->user_id;
        
        if (!$loserId) {
            Log::warning('ProcessX1Result: Não foi possível determinar perdedor (Lógica Inconsistente)', [
                'room_id' => $this->room->id,
            ]);
            return;
        }

        // --- PAYOUT LOGIC (WINNER) ---
        $winnerUser = User::find($winnerId);
        if ($winnerUser) {
            $prizeAmount = $this->room->prize_total;
            
            $winnerUser->balance += $prizeAmount;
            $winnerUser->save();

            Transaction::create([
                'user_id' => $winnerUser->id,
                'amount' => $prizeAmount,
                'charge' => 0,
                'post_balance' => $winnerUser->balance,
                'trx_type' => '+',
                'details' => 'Prêmio X1 - Vitória na Sala #' . $this->room->id,
                'trx' => getTrx(),
                'remark' => 'x1_win',
            ]);
            
            Log::info("ProcessX1Result: Prêmio pago ao vencedor #{$winnerId}", ['amount' => $prizeAmount]);
        }

        // Criar registro do resultado
        $result = X1Result::create([
            'x1_room_id' => $this->room->id,
            'winner_user_id' => $winnerId,
            'payload' => [
                'loser_user_id' => $loserId,
                'prize_total' => $this->room->prize_total,
                'valor_entrada' => $this->room->valor_entrada,
                'modalidade_id' => $this->room->modalidade_id,
                'processed_by' => 'job',
                'tie_breaker_applied' => true
            ],
            'processed_at' => now(),
            'prize_paid_at' => now(),
        ]);

        // ✅ Atualizar status da sala para finalizada
        $this->room->update([
            'status' => 'finished',
            'finished_at' => now(),
        ]);

        // INTEGRAÇÃO AFILIADOS
        try {
            $totalEntry = $this->room->valor_entrada * 2;
            $platformFee = $totalEntry - $this->room->prize_total;

            if ($platformFee > 0) {
                app(\App\Services\AffiliateCommissionService::class)->processX1Commission(
                    $this->room->id,
                    $winnerId,
                    $loserId,
                    $platformFee
                );
                Log::info('ProcessX1Result: Comissões de afiliados processadas', ['room_id' => $this->room->id]);
            }
        } catch (\Exception $e) {
            Log::error('ProcessX1Result: Erro ao processar afiliados', [
                'room_id' => $this->room->id,
                'error' => $e->getMessage()
            ]);
        }

        // Atualizar estatísticas via serviço
        try {
            $statsService = app(X1StatsService::class);
            $statsService->recordX1Result(
                $this->room,
                $winnerId,
                $loserId,
                (float) $this->room->prize_total,
                (float) $this->room->valor_entrada
            );

            Log::info('ProcessX1Result: Estatísticas atualizadas', [
                'room_id' => $this->room->id,
                'winner_id' => $winnerId,
                'loser_id' => $loserId,
            ]);
        } catch (\Exception $e) {
            Log::error('ProcessX1Result: Erro ao atualizar estatísticas', [
                'room_id' => $this->room->id,
                'error' => $e->getMessage(),
            ]);
        }

        // TODO: Disparar job de atualização de ranking se necessário
        // ProcessX1RankingUpdate::dispatch($this->room->modalidade_id);
    }

    /**
     * Determine the winner based on real stats.
     */
    protected function determineWinner($participants): ?int
    {
        $data = [];

        foreach ($participants as $participant) {
            // Check for manual override first
            if (is_array($participant->result) && isset($participant->result['is_winner']) && $participant->result['is_winner']) {
                return $participant->user_id;
            }

            $score = 0;

            if ($participant->competitor_id) {
                // Single Competitor
                $query = \App\Models\CompetitorContextStat::where('competitor_id', $participant->competitor_id)
                    ->where('rodeio_id', $this->room->rodeio_id)
                    ->where('modalidade_id', $this->room->modalidade_id);
                
                if ($this->room->divisao) {
                    $query->where('divisao', $this->room->divisao);
                }

                // Get the most recently updated stat (most relevant to current live event)
                $stat = $query->orderBy('last_updated_at', 'desc')->first();
                
                $score = $stat ? $stat->pontuacao_total : 0;

            } elseif ($participant->competitor_group_id) {
                // Competitor Group
                $group = \App\Models\ModalidadeCompetitorGroup::with('members')->find($participant->competitor_group_id);
                
                if ($group) {
                    $memberIds = $group->members->pluck('id');
                    
                    $query = \App\Models\CompetitorContextStat::whereIn('competitor_id', $memberIds)
                        ->where('rodeio_id', $this->room->rodeio_id)
                        ->where('modalidade_id', $this->room->modalidade_id);

                    if ($this->room->divisao) {
                        $query->where('divisao', $this->room->divisao);
                    }
                    
                    $score = $query->sum('pontuacao_total');
                }
            }

            $data[] = [
                'user_id' => $participant->user_id,
                'score' => $score,
                'created_at' => $participant->created_at,
            ];
        }

        // Sort by Score DESC only (to detect true draw)
        usort($data, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Check for Draw (Top 2 have exact same score)
        if (count($data) >= 2 && $data[0]['score'] === $data[1]['score']) {
            return null; // Return null to trigger refund logic
        }

        // Top 1 is the winner
        return $data[0]['user_id'] ?? null;
    }
}
