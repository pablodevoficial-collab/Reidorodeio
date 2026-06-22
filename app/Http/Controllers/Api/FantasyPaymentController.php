<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FantasyPayment;
use App\Models\FantasyLeague;
use App\Models\FantasyTeam;
use App\Services\FantasyTeamEntryRuleService;
use App\Services\MercadoPagoService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FantasyPaymentController extends Controller
{
    private const MAX_ACTIVE_PIX_PER_LEAGUE = 10;
    private const PIX_EXPIRATION_MINUTES = 5;

    private function resolveMercadoPagoNotificationUrl(): ?string
    {
        $url = route('ipn.MercadoPago');

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return null;
        }

        $normalizedHost = strtolower((string) $host);
        if (in_array($normalizedHost, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($normalizedHost, '.local')) {
            return null;
        }

        if (filter_var($normalizedHost, FILTER_VALIDATE_IP)) {
            $isPublicIp = filter_var(
                $normalizedHost,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );

            if (!$isPublicIp) {
                return null;
            }
        }

        return $url;
    }

    private function expireStalePendingPayments(int $leagueId): void
    {
        FantasyPayment::query()
            ->where('fantasy_league_id', $leagueId)
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }

    private function activePendingPaymentsCount(int $leagueId): int
    {
        return FantasyPayment::query()
            ->where('fantasy_league_id', $leagueId)
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->count();
    }

    private function queuedPaymentPosition(FantasyPayment $payment): ?int
    {
        if ($payment->status !== 'queued') {
            return null;
        }

        $ids = FantasyPayment::query()
            ->where('fantasy_league_id', $payment->fantasy_league_id)
            ->where('status', 'queued')
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $index = array_search($payment->id, $ids, true);

        return $index === false ? null : $index + 1;
    }

    private function buildPaymentResponse(FantasyPayment $payment)
    {
        $payment->refresh();
        $payload = $payment->payload ?? [];
        $queuePosition = $this->queuedPaymentPosition($payment);
        $estimatedWaitMinutes = $queuePosition ? (int) (ceil($queuePosition / self::MAX_ACTIVE_PIX_PER_LEAGUE) * self::PIX_EXPIRATION_MINUTES) : null;

        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'payment_id' => $payment->id,
            'preference_id' => $payment->provider_preference_id,
            'qr_code' => $payload['qr_code'] ?? null,
            'qr_code_base64' => $payload['qr_code_base64'] ?? null,
            'expires_at' => $payment->expires_at?->toIso8601String(),
            'amount' => $payment->amount,
            'queue_position' => $queuePosition,
            'estimated_wait_minutes' => $estimatedWaitMinutes,
            'active_limit' => self::MAX_ACTIVE_PIX_PER_LEAGUE,
        ]);
    }

    private function activateQueuedPayment(FantasyPayment $payment): FantasyPayment
    {
        if ($payment->status !== 'queued') {
            return $payment->fresh();
        }

        if ($this->activePendingPaymentsCount((int) $payment->fantasy_league_id) >= self::MAX_ACTIVE_PIX_PER_LEAGUE) {
            return $payment->fresh();
        }

        $payment->loadMissing(['user', 'fantasyLeague']);
        $user = $payment->user;
        $league = $payment->fantasyLeague;

        if (!$user || !$league) {
            $payment->update(['status' => 'failed']);
            throw new \RuntimeException('Pagamento sem usuário ou liga vinculados.');
        }

        $externalRef = $payment->external_reference ?: ('fantasy:' . $league->id . '|user:' . $user->id . '|' . Str::random(8));
        $preferenceId = $payment->provider_preference_id ?: Str::uuid()->toString();

        $payerEmail = strtolower(trim((string) ($user->email ?? '')));
        if (!filter_var($payerEmail, FILTER_VALIDATE_EMAIL) || str_ends_with($payerEmail, '@cadastro.local') || str_ends_with($payerEmail, '@deleted.local') || str_ends_with($payerEmail, '@deleted.invalid')) {
            $payerEmail = 'noreply+fantasy' . substr(md5($externalRef), 0, 10) . '@reidorodeio.com.br';
        }

        $pixData = [
            'transaction_amount' => (float) $payment->amount,
            'description' => 'Fantasy ' . $league->name . ' - ' . config('app.name'),
            'payment_method_id' => 'pix',
            'payer' => [
                'email' => $payerEmail,
                'first_name' => $user->firstname ?? $user->name ?? 'Cliente',
                'last_name' => $user->lastname ?? '',
                'entity_type' => 'individual',
            ],
            'external_reference' => $externalRef,
        ];

        $notificationUrl = $this->resolveMercadoPagoNotificationUrl();
        if ($notificationUrl) {
            $pixData['notification_url'] = $notificationUrl;
        }

        $mpService = app(MercadoPagoService::class);
        $pixPayment = $mpService->createPixPayment($pixData);

        $payload = $payment->payload ?? [];
        $payload['qr_code'] = data_get($pixPayment, 'point_of_interaction.transaction_data.qr_code');
        $payload['qr_code_base64'] = data_get($pixPayment, 'point_of_interaction.transaction_data.qr_code_base64');
        $payload['pix_payment'] = $pixPayment;

        $payment->update([
            'external_reference' => $externalRef,
            'provider_preference_id' => $preferenceId,
            'provider_payment_id' => $pixPayment['id'] ?? null,
            'payload' => $payload,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(self::PIX_EXPIRATION_MINUTES),
        ]);

        return $payment->fresh();
    }

    private function promoteQueuedPayments(int $leagueId, bool $swallowErrors = true): void
    {
        $this->expireStalePendingPayments($leagueId);

        $availableSlots = self::MAX_ACTIVE_PIX_PER_LEAGUE - $this->activePendingPaymentsCount($leagueId);
        if ($availableSlots <= 0) {
            return;
        }

        $queuedPayments = FantasyPayment::query()
            ->where('fantasy_league_id', $leagueId)
            ->where('status', 'queued')
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit($availableSlots)
            ->get();

        foreach ($queuedPayments as $queuedPayment) {
            try {
                $this->activateQueuedPayment($queuedPayment);
            } catch (\Throwable $exception) {
                \Log::error('Falha ao promover pagamento fantasy da fila PIX', [
                    'payment_id' => $queuedPayment->id,
                    'league_id' => $leagueId,
                    'error' => $exception->getMessage(),
                ]);

                $queuedPayment->update(['status' => 'failed']);

                if (!$swallowErrors) {
                    throw $exception;
                }
            }
        }
    }

    private function userHasPremiumAccess($user): bool
    {
        if (!$user || !method_exists($user, 'isPremium')) {
            return false;
        }

        try {
            return (bool) $user->isPremium();
        } catch (\Throwable $exception) {
            \Log::warning('Falha ao verificar assinatura premium do usuário no fluxo fantasy', [
                'user_id' => $user->id ?? null,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function ensureLeagueEntryAllowed(FantasyLeague $league, $user)
    {
        if (!(bool) $league->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Liga inativa',
            ], 422);
        }

        if ($league->rodeio_id && Schema::hasTable('rodeios')) {
            $rodeio = \App\Models\Rodeio::query()->select(['id', 'status_transmissao'])->find($league->rodeio_id);
            if ($rodeio && strtolower((string) ($rodeio->status_transmissao ?? '')) === 'finalizado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Evento finalizado. Não é possível criar novas equipes.',
                ], 422);
            }
        }

        if ((bool) $league->is_premium && !$this->userHasPremiumAccess($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Liga Premium requer assinatura ativa',
            ], 403);
        }

        if ($league->max_users) {
            $teamCount = FantasyTeam::query()
                ->where('fantasy_league_id', $league->id)
                ->where('is_active', true)
                ->count();

            if ($teamCount >= (int) $league->max_users) {
                return response()->json([
                    'success' => false,
                    'message' => 'Liga atingiu o limite de participantes',
                ], 422);
            }
        }

        return null;
    }

    private function isLegacySingleTeamConstraintViolation(\Throwable $exception): bool
    {
        if ($exception instanceof QueryException) {
            $message = strtolower($exception->getMessage());
            $errorInfo = $exception->errorInfo ?? [];
            $driverCode = (int) ($errorInfo[1] ?? 0);

            if ($driverCode === 1062) {
                return str_contains($message, 'fantasy_teams_unique_user_per_league')
                    || (str_contains($message, 'fantasy_teams') && str_contains($message, 'fantasy_league_id'))
                    || (str_contains($message, 'fantasy_teams') && str_contains($message, 'user_id'));
            }

            return str_contains($message, 'fantasy_teams_unique_user_per_league')
                || (str_contains($message, 'duplicate entry') && str_contains($message, 'fantasy_teams'))
                || (str_contains($message, 'integrity constraint violation') && str_contains($message, 'fantasy_teams'));
        }

        return false;
    }

    private function isFantasyEntryIntegrityViolation(\Throwable $exception): bool
    {
        if (!$exception instanceof QueryException) {
            return false;
        }

        $message = strtolower($exception->getMessage());
        $errorInfo = $exception->errorInfo ?? [];
        $driverCode = (int) ($errorInfo[1] ?? 0);

        return $driverCode === 1062
            || str_contains($message, 'duplicate entry')
            || str_contains($message, 'integrity constraint violation');
    }

    private function buildDirectEntryFailureResponse(\Throwable $exception, FantasyLeague $league, $user)
    {
        if ($this->isLegacySingleTeamConstraintViolation($exception)) {
            \Log::warning('Constraint antiga impedindo múltiplas equipes no bolão', [
                'league_id' => $league->id,
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Este bolão ainda está com a trava antiga de 1 equipe por usuário. Rode a migration pendente para liberar múltiplos bilhetes.',
            ], 422);
        }

        if ($this->isFantasyEntryIntegrityViolation($exception)) {
            \Log::warning('Erro de integridade ao criar equipe fantasy diretamente', [
                'league_id' => $league->id,
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
                'sql_state' => $exception instanceof QueryException ? ($exception->errorInfo[0] ?? null) : null,
                'driver_code' => $exception instanceof QueryException ? ($exception->errorInfo[1] ?? null) : null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível confirmar esta equipe porque o banco rejeitou esta combinação. Tente trocar os competidores e repetir.',
            ], 422);
        }

        \Log::error('Erro inesperado ao criar equipe fantasy diretamente', [
            'league_id' => $league->id,
            'user_id' => $user->id,
            'error' => $exception->getMessage(),
            'exception' => get_class($exception),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Não foi possível confirmar sua entrada agora.',
        ], 500);
    }

    /**
     * Initiate payment for a Fantasy Team
     * POST /api/fantasy/leagues/{leagueId}/teams/pay
     */
    public function initiatePayment(Request $request, int $leagueId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Não autenticado'], 401);
        }

        try {
            $validated = $request->validate([
                'competitor_ids' => 'required|array|size:4',
                'competitor_ids.*' => 'integer|min:1',
                'captain_id' => 'nullable|integer|min:1',
                'platform' => 'nullable|string|max:20',
            ]);

            $league = FantasyLeague::find($leagueId);
            if (!$league) {
                return response()->json(['success' => false, 'message' => 'Liga não encontrada'], 404);
            }

            // 🚫 Verificar se inscrições estão abertas
            if (!$league->isRegistrationOpen()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inscrições encerradas para esta liga',
                    'registration_closed' => true,
                    'deadline' => $league->registration_deadline?->toIso8601String(),
                ], 403);
            }

            $entryBlockedResponse = $this->ensureLeagueEntryAllowed($league, $user);
            if ($entryBlockedResponse instanceof \Illuminate\Http\JsonResponse) {
                return $entryBlockedResponse;
            }

            // Check if league is free (price = 0) or premium-only
            $leaguePrice = (float) ($league->price ?? 0);
            $isPremiumOnly = (bool) $league->is_premium;

            // If premium-only and user is premium, it's free
            if ($isPremiumOnly && $this->userHasPremiumAccess($user)) {
                $leaguePrice = 0;
            }

            // If it's free, create team directly
            if ($leaguePrice <= 0) {
                return $this->createTeamDirectly($request, $league, $user, $validated);
            }

            $competitorIds = array_values(array_map('intval', $validated['competitor_ids']));
            $teamRuleViolation = app(FantasyTeamEntryRuleService::class)
                ->validateForUser((int) $leagueId, (int) $user->id, $competitorIds);
            if ($teamRuleViolation) {
                return response()->json($teamRuleViolation, 422);
            }

            $selectionValidation = $this->validateSelectionAvailability($league, $validated['competitor_ids']);
            if ($selectionValidation instanceof \Illuminate\Http\JsonResponse) {
                return $selectionValidation;
            }

            $storeService = app(\App\Services\AppStoreService::class);
            $eligibleVoucher = $storeService->eligibleFantasyVoucher($user, $leaguePrice);

            if ($eligibleVoucher) {
                return $this->createTeamDirectly($request, $league, $user, $validated, [
                    'voucher' => $eligibleVoucher,
                    'success_message' => 'Voucher aplicado: sua entrada deste bolão foi liberada sem PIX.',
                ]);
            }

            if ((float) ($user->balance ?? 0) >= $leaguePrice) {
                return $this->createTeamDirectly($request, $league, $user, $validated, [
                    'wallet_charge_amount' => $leaguePrice,
                    'success_message' => 'Saldo da carteira usado. Equipe cadastrada sem PIX.',
                ]);
            }

            $this->expireStalePendingPayments($leagueId);
            $this->promoteQueuedPayments($leagueId);

            // Check if user already has pending payment for this league
            $existingPayment = FantasyPayment::where('fantasy_league_id', $leagueId)
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'queued'])
                ->where(function ($query) {
                    $query->where('status', 'queued')
                        ->orWhere(function ($pendingQuery) {
                            $pendingQuery->where('status', 'pending')
                                ->where('expires_at', '>', now());
                        });
                })
                ->first();

            if ($existingPayment) {
                if ($existingPayment->status === 'queued') {
                    $existingPayment = $this->activateQueuedPayment($existingPayment);
                }

                return $this->buildPaymentResponse($existingPayment)
                    ->setData(array_merge($this->buildPaymentResponse($existingPayment)->getData(true), ['payment_exists' => true]));
            }

            // Create payment record
            $externalRef = 'fantasy:' . $leagueId . '|user:' . $user->id . '|' . Str::random(8);
            $preferenceId = Str::uuid()->toString();

            $payment = FantasyPayment::create([
                'fantasy_league_id' => $leagueId,
                'user_id' => $user->id,
                'amount' => $leaguePrice,
                'provider' => 'mercadopago',
                'external_reference' => $externalRef,
                'provider_preference_id' => $preferenceId,
                'status' => 'queued',
                'expires_at' => null,
                'payload' => [
                    'competitor_ids' => $validated['competitor_ids'],
                    'captain_id' => $validated['captain_id'] ?? $validated['competitor_ids'][0],
                    'team_name' => 'Equipe ' . strtoupper(Str::random(6)),
                ],
            ]);

            try {
                $payment = $this->activateQueuedPayment($payment);
                return $this->buildPaymentResponse($payment);
            } catch (\Exception $e) {
                \Log::error('Fantasy PIX Error', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'league_id' => $leagueId,
                ]);

                $payment->update(['status' => 'failed']);

                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao gerar PIX: ' . $e->getMessage()
                ], 500);
            }
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?: 'Não foi possível iniciar sua entrada.';

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        } catch (\Throwable $exception) {
            \Log::error('Erro inesperado em teams/pay do bolão', [
                'league_id' => $leagueId,
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
                'exception' => get_class($exception),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível iniciar sua entrada agora.',
            ], 500);
        }
    }

    /**
     * Check payment status
     * GET /api/fantasy/payments/{preferenceId}/status
     */
    public function checkStatus(Request $request, string $preferenceId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Não autenticado'], 401);
        }

        $payment = FantasyPayment::where('provider_preference_id', $preferenceId)
            ->where('user_id', $user->id)
            ->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Pagamento não encontrado'], 404);
        }

        $this->expireStalePendingPayments((int) $payment->fantasy_league_id);
        $this->promoteQueuedPayments((int) $payment->fantasy_league_id);
        $payment->refresh();

        // Check if expired
        if ($payment->isExpired() && $payment->status === 'pending') {
            $payment->update(['status' => 'expired']);
            return response()->json([
                'success' => true,
                'status' => 'expired',
                'message' => 'Pagamento expirado',
            ]);
        }

        if ($payment->status === 'queued') {
            $payment = $this->activateQueuedPayment($payment);
            if ($payment->status === 'queued') {
                return $this->buildPaymentResponse($payment);
            }
        }

        // If pending and has payment_id, check with MercadoPago
        if ($payment->status === 'pending' && $payment->provider_payment_id) {
            try {
                $mpService = app(MercadoPagoService::class);
                $mpPayment = $mpService->fetchPayment($payment->provider_payment_id);
                $mpStatus = $mpPayment['status'] ?? 'pending';

                if ($mpStatus === 'approved' && $payment->status !== 'approved') {
                    // Process approval!
                    $this->processApproval($payment);
                }
            } catch (\Exception $e) {
                \Log::error('Fantasy payment status check error', [
                    'error' => $e->getMessage(),
                    'payment_id' => $payment->id,
                ]);
                
                // Se o erro é de constraint (time duplicado), marcar pagamento como aprovado
                // O time já foi criado em tentativa anterior
                if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'Integrity constraint')) {
                    $payment->update([
                        'status' => 'approved',
                        'paid_at' => $payment->paid_at ?? now(),
                    ]);
                }
            }
        }

        // Reload payment
        $payment->refresh();

        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'team_id' => $payment->fantasy_team_id,
            'expires_at' => $payment->expires_at?->toIso8601String(),
            'queue_position' => $this->queuedPaymentPosition($payment),
            'estimated_wait_minutes' => $this->queuedPaymentPosition($payment)
                ? (int) (ceil($this->queuedPaymentPosition($payment) / self::MAX_ACTIVE_PIX_PER_LEAGUE) * self::PIX_EXPIRATION_MINUTES)
                : null,
            'qr_code' => data_get($payment->payload, 'qr_code'),
            'qr_code_base64' => data_get($payment->payload, 'qr_code_base64'),
        ]);
    }

    /**
     * Cancel pending payment
     * POST /api/fantasy/payments/{preferenceId}/cancel
     */
    public function cancelPayment(Request $request, string $preferenceId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Não autenticado'], 401);
        }

        $payment = FantasyPayment::where('provider_preference_id', $preferenceId)
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'queued'])
            ->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Pagamento não encontrado'], 404);
        }

        // Cancel with MercadoPago if has payment_id
        if ($payment->provider_payment_id) {
            try {
                $mpService = app(MercadoPagoService::class);
                $mpService->cancelPayment($payment->provider_payment_id);
            } catch (\Exception $e) {
                \Log::warning('Could not cancel MP payment', ['error' => $e->getMessage()]);
            }
        }

        DB::transaction(function () use ($payment) {
            if ($payment->fantasy_team_id) {
                FantasyTeam::query()
                    ->where('id', $payment->fantasy_team_id)
                    ->update(['is_active' => false]);
            }

            $payload = $payment->payload ?? [];
            unset($payload['qr_code'], $payload['qr_code_base64'], $payload['pix_payment']);

            $payment->update([
                'status' => 'cancelled',
                'expires_at' => null,
                'provider_payment_id' => null,
                'payload' => $payload,
            ]);
        });

        $this->promoteQueuedPayments((int) $payment->fantasy_league_id);

        return response()->json([
            'success' => true,
            'message' => 'Equipe removida da fila do PIX.',
        ]);
    }

    /**
     * Process approved payment - create the team
     */
    public function processApproval(FantasyPayment $payment): void
    {
        // Evitar reprocessamento se já foi aprovado
        if ($payment->status === 'approved' || $payment->fantasy_team_id) {
            return;
        }

        DB::transaction(function () use ($payment) {
            // Re-check dentro da transaction para evitar race condition
            $payment->refresh();
            if ($payment->status === 'approved' || $payment->fantasy_team_id) {
                return;
            }

            $payload = $payment->payload ?? [];
            $competitorIds = $payload['competitor_ids'] ?? [];
            $captainId = $payload['captain_id'] ?? ($competitorIds[0] ?? null);
            $teamName = $payload['team_name'] ?? 'Equipe Fantasy';

            if (count($competitorIds) !== 4) {
                \Log::error('Fantasy payment invalid competitor count', ['payment_id' => $payment->id]);
                $payment->update(['status' => 'failed']);
                return;
            }

            $teamRuleViolation = app(FantasyTeamEntryRuleService::class)
                ->validateForUser((int) $payment->fantasy_league_id, (int) $payment->user_id, $competitorIds);
            if ($teamRuleViolation) {
                \Log::warning('Fantasy payment blocked by team entry rule at approval', [
                    'payment_id' => $payment->id,
                    'league_id' => $payment->fantasy_league_id,
                    'user_id' => $payment->user_id,
                    'team_rule' => $teamRuleViolation['team_rule'] ?? null,
                ]);
                $payment->update(['status' => 'failed']);
                return;
            }

            // Create the team
            $team = FantasyTeam::create([
                'fantasy_league_id' => $payment->fantasy_league_id,
                'user_id' => $payment->user_id,
                'team_name' => $teamName,
                'total_points' => 0,
                'is_active' => true,
            ]);

            // Attach competitors
            if (Schema::hasTable('fantasy_team_competitors')) {
                foreach ($competitorIds as $competitorId) {
                    $isCaptain = $captainId && (int) $competitorId === (int) $captainId;
                    $team->competitorsRelation()->attach($competitorId, [
                        'role' => 'titular',
                        'is_captain' => $isCaptain,
                        'multiplier' => $isCaptain ? 1.5 : 1,
                    ]);
                }
            }

            // Update payment
            $payment->update([
                'status' => 'approved',
                'paid_at' => now(),
                'fantasy_team_id' => $team->id,
            ]);

            $this->promoteQueuedPayments((int) $payment->fantasy_league_id);

            try {
                app(\App\Services\AppCommunityFeedService::class)
                    ->publishFantasyTeamJoined($team);
            } catch (\Throwable $exception) {
                \Log::warning('Falha ao publicar entrada do bolão no feed da comunidade', [
                    'payment_id' => $payment->id,
                    'team_id' => $team->id,
                    'league_id' => $payment->fantasy_league_id,
                    'error' => $exception->getMessage(),
                ]);
            }

            \Log::info('✅ Fantasy team created via payment', [
                'payment_id' => $payment->id,
                'team_id' => $team->id,
                'league_id' => $payment->fantasy_league_id,
            ]);
        });
    }

    /**
     * Create team directly (for free/premium leagues)
     */
    private function createTeamDirectly(Request $request, FantasyLeague $league, $user, array $validated, array $options = [])
    {
        $entryBlockedResponse = $this->ensureLeagueEntryAllowed($league, $user);
        if ($entryBlockedResponse instanceof \Illuminate\Http\JsonResponse) {
            return $entryBlockedResponse;
        }

        // Reuse existing logic from FantasyLeagueApiController
        $competitorIds = array_values(array_unique(array_map('intval', $validated['competitor_ids'])));
        $captainId = isset($validated['captain_id']) ? (int) $validated['captain_id'] : $competitorIds[0];
        $walletChargeAmount = (float) ($options['wallet_charge_amount'] ?? 0);
        $usedWallet = $walletChargeAmount > 0;
        $voucher = $options['voucher'] ?? null;
        $appliedVoucher = null;

        $teamRuleViolation = app(FantasyTeamEntryRuleService::class)
            ->validateForUser((int) $league->id, (int) $user->id, $competitorIds);
        if ($teamRuleViolation) {
            return response()->json($teamRuleViolation, 422);
        }

        $selectionValidation = $this->validateSelectionAvailability($league, $competitorIds);
        if ($selectionValidation instanceof \Illuminate\Http\JsonResponse) {
            return $selectionValidation;
        }

        // Calculate originality
        $originality = [
            'originality_factor' => 1.00,
            'similarity_count' => 0,
        ];

        try {
            $originalityService = app(\App\Services\FantasyOriginalityService::class);
            $originality = $originalityService->calculateOriginality($league->id, $competitorIds);
        } catch (\Throwable $exception) {
            \Log::warning('Falha ao calcular originalidade da equipe fantasy; usando fallback neutro', [
                'league_id' => $league->id,
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
                'exception' => get_class($exception),
            ]);
        }

        try {
            $team = DB::transaction(function () use ($league, $user, $competitorIds, $captainId, $originality, $walletChargeAmount, $usedWallet, $voucher, &$appliedVoucher) {
                if ($usedWallet) {
                    app(\App\Services\AppStoreService::class)->debitFantasyLeagueWallet(
                        user: $user,
                        league: $league,
                        amount: $walletChargeAmount,
                        metadata: [
                            'competitor_ids' => $competitorIds,
                            'captain_id' => $captainId,
                        ],
                    );
                }

                if ($voucher) {
                    $appliedVoucher = app(\App\Services\AppStoreService::class)->consumeFantasyVoucher($voucher, $league);
                }

                $teamData = [
                    'fantasy_league_id' => $league->id,
                    'user_id' => $user->id,
                    'team_name' => 'Equipe ' . strtoupper(Str::random(6)),
                    'total_points' => 0,
                    'is_active' => true,
                ];

                // Backward compatibility: some databases may not have these columns yet.
                if (Schema::hasColumn('fantasy_teams', 'originality_factor')) {
                    $teamData['originality_factor'] = $originality['originality_factor'];
                }
                if (Schema::hasColumn('fantasy_teams', 'similarity_count')) {
                    $teamData['similarity_count'] = $originality['similarity_count'];
                }

                $team = FantasyTeam::create($teamData);

                if (Schema::hasTable('fantasy_team_competitors')) {
                    foreach ($competitorIds as $competitorId) {
                        $isCaptain = (int) $competitorId === (int) $captainId;
                        $team->competitorsRelation()->attach($competitorId, [
                            'role' => 'titular',
                            'is_captain' => $isCaptain,
                            'multiplier' => $isCaptain ? 1.5 : 1,
                        ]);
                    }
                }

                return $team;
            });
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?: 'Não foi possível confirmar sua entrada.';

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        } catch (\Throwable $exception) {
            return $this->buildDirectEntryFailureResponse($exception, $league, $user);
        }

        try {
            app(\App\Services\AppCommunityFeedService::class)
                ->publishFantasyTeamJoined($team);
        } catch (\Throwable $exception) {
            \Log::warning('Falha ao publicar equipe fantasy no feed da comunidade', [
                'team_id' => $team->id,
                'league_id' => $league->id,
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'free_entry' => true,
            'wallet_applied' => $usedWallet,
            'voucher_applied' => $appliedVoucher !== null,
            'voucher' => $appliedVoucher ? [
                'id' => (int) $appliedVoucher->id,
                'title' => (string) $appliedVoucher->title,
                'credit_amount' => (float) $appliedVoucher->credit_amount,
            ] : null,
            'team_id' => $team->id,
            'message' => $options['success_message'] ?? 'Equipe criada com sucesso!',
        ], 201);
    }

    private function validateSelectionAvailability(FantasyLeague $league, array $competitorIds)
    {
        $competitorIds = array_values(array_unique(array_map('intval', $competitorIds)));

        if (count($competitorIds) !== 4) {
            return response()->json([
                'success' => false,
                'message' => 'Selecione exatamente 4 competidores',
            ], 422);
        }

        if (!Schema::hasTable('competitor_modalidade') || !Schema::hasTable('competitors')) {
            return [
                'available_ids' => $competitorIds,
            ];
        }

        $allowedIdsQuery = DB::table('competitor_modalidade as cm')
            ->join('competitors as c', 'c.id', '=', 'cm.competitor_id')
            ->where('cm.modalidade_id', (int) $league->modalidade_id)
            ->where('c.status', 'ativo')
            ->whereIn('cm.competitor_id', $competitorIds)
            ->where('cm.disponivel_participacao', 1);

        $hasPivotDivisao = Schema::hasTable('competitor_modalidade') && Schema::hasColumn('competitor_modalidade', 'divisao');
        $leagueDivisao = trim((string) ($league->divisao ?? ''));
        $modalidade = $league->modalidade;
        $isClassificatoria = $modalidade && in_array($modalidade->status, ['classificatoria', 'programado'], true);
        $hasAssignedDivisions = false;

        if ($hasPivotDivisao && !$isClassificatoria) {
            $hasAssignedDivisions = DB::table('competitor_modalidade')
                ->where('modalidade_id', (int) $league->modalidade_id)
                ->whereNotNull('divisao')
                ->where('divisao', '!=', '')
                ->exists();
        }

        if ($leagueDivisao !== '' && $hasPivotDivisao && !$isClassificatoria && $hasAssignedDivisions) {
            $allowedIdsQuery->where('cm.divisao', $leagueDivisao);
        }

        $allowedIds = $allowedIdsQuery->pluck('cm.competitor_id')->map(fn ($value) => (int) $value)->all();
        sort($allowedIds);
        $selectedSorted = $competitorIds;
        sort($selectedSorted);

        if ($allowedIds !== $selectedSorted) {
            return response()->json([
                'success' => false,
                'message' => 'Um ou mais competidores não estão disponíveis para esta liga',
            ], 422);
        }

        return [
            'available_ids' => $allowedIds,
        ];
    }

}
