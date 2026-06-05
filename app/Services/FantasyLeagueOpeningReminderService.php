<?php

namespace App\Services;

use App\Models\FantasyLeague;
use App\Models\FantasyLeagueOpeningReminder;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class FantasyLeagueOpeningReminderService
{
    private const SLOT_CUSTOM = 'custom';

    private const FIXED_SLOTS = ['20', '50', '100'];

    public function normalizeSlot(?string $slot): ?string
    {
        $normalized = strtolower(trim((string) $slot));

        if ($normalized === self::SLOT_CUSTOM) {
            return self::SLOT_CUSTOM;
        }

        if (in_array($normalized, self::FIXED_SLOTS, true)) {
            return $normalized;
        }

        return null;
    }

    public function slotKeyForLeague(FantasyLeague $league): ?string
    {
        if ((bool) ($league->is_premium ?? false)) {
            return null;
        }

        $price = round((float) ($league->price ?? 0), 2);
        foreach (self::FIXED_SLOTS as $slot) {
            if ($price === (float) $slot) {
                return $slot;
            }
        }

        return self::SLOT_CUSTOM;
    }

    public function slotLabel(string $slot): string
    {
        return match ($this->normalizeSlot($slot)) {
            '20' => 'R$20',
            '50' => 'R$50',
            '100' => 'R$100',
            default => 'Personalizado',
        };
    }

    public function subscribe(string $slot, string $emailAddress, ?User $user = null, ?string $name = null): FantasyLeagueOpeningReminder
    {
        $slotKey = $this->normalizeSlot($slot);
        if (!$slotKey) {
            throw new \InvalidArgumentException('Slot de bolão inválido para notificação.');
        }

        $email = mb_strtolower(trim($emailAddress));
        $displayName = $this->resolveReminderName($user, $name, $email);

        if (!$this->usesDatabaseStorage()) {
            return $this->subscribeUsingFallback($slotKey, $email, $displayName, $user);
        }

        $reminder = FantasyLeagueOpeningReminder::query()->firstOrNew([
            'slot_key' => $slotKey,
            'email' => $email,
        ]);

        if ($user && !$reminder->user_id) {
            $reminder->user_id = $user->id;
        }

        $reminder->name = $displayName;
        $reminder->opened_notification_sent_at = null;
        $reminder->save();

        return $reminder->fresh(['user']) ?? $reminder;
    }

    public function canSubscribe(string $slot): bool
    {
        $slotKey = $this->normalizeSlot($slot);
        if (!$slotKey) {
            return false;
        }

        return !$this->hasOpenLeagueForSlot($slotKey);
    }

    public function subscribedSlotsFor(?User $user = null, ?string $emailAddress = null): array
    {
        $email = mb_strtolower(trim((string) $emailAddress));

        if (!$user && $email === '') {
            return [];
        }

        if (!$this->usesDatabaseStorage()) {
            return $this->subscribedSlotsUsingFallback($user, $email);
        }

        $query = FantasyLeagueOpeningReminder::query();
        $query->where(function ($builder) use ($user, $email) {
            $hasCondition = false;

            if ($user) {
                $builder->where('user_id', $user->id);
                $hasCondition = true;
            }

            if ($email !== '') {
                if ($hasCondition) {
                    $builder->orWhereRaw('LOWER(email) = ?', [$email]);
                } else {
                    $builder->whereRaw('LOWER(email) = ?', [$email]);
                }
            }
        });

        $query->whereNull('opened_notification_sent_at');

        return $query
            ->pluck('slot_key')
            ->map(fn ($value) => (string) $value)
            ->filter(fn ($value) => $this->normalizeSlot($value) !== null)
            ->unique()
            ->values()
            ->all();
    }

    public function sendLeagueOpenedNotifications(FantasyLeague $league): int
    {
        $slotKey = $this->slotKeyForLeague($league);
        if (!$slotKey) {
            Log::info('[FantasyLeagueOpeningReminder] Liga ignorada para alerta de abertura', [
                'league_id' => $league->id,
                'reason' => 'invalid_slot_or_premium',
                'is_premium' => (bool) ($league->is_premium ?? false),
                'price' => (float) ($league->price ?? 0),
            ]);

            return 0;
        }

        if (!$this->isLeagueOpenForNotifications($league)) {
            Log::info('[FantasyLeagueOpeningReminder] Liga ainda nao elegivel para alerta de abertura', [
                'league_id' => $league->id,
                'slot_key' => $slotKey,
                'is_active' => (bool) ($league->is_active ?? false),
                'status' => (string) ($league->status ?? ''),
                'allow_late_registration' => (bool) ($league->allow_late_registration ?? false),
                'registration_deadline' => optional($league->registration_deadline)?->toIso8601String(),
            ]);

            return 0;
        }

        if (!$this->usesDatabaseStorage()) {
            return $this->sendLeagueOpenedNotificationsUsingFallback($league, $slotKey);
        }

        $reminders = FantasyLeagueOpeningReminder::query()
            ->with('user')
            ->where('slot_key', $slotKey)
            ->whereNull('opened_notification_sent_at')
            ->get();

        $sentCount = 0;

        foreach ($reminders as $reminder) {
            try {
                $message = view('emails.fantasy.reminder-league-opened', [
                    'league' => $league,
                    'slotLabel' => $this->slotLabel($slotKey),
                    'emailPrizeSummary' => $this->buildLeagueOpenedEmailPrizeSummary($league),
                    'ctaUrl' => route('home'),
                ])->render();

                $subject = $this->leagueOpenedEmailSubject($league, $slotKey);
                $this->deliverEmail($reminder, $subject, $message);

                $reminder->forceFill([
                    'opened_notification_sent_at' => now(),
                ])->save();

                $sentCount++;
            } catch (\Throwable $exception) {
                Log::warning('[FantasyLeagueOpeningReminder] Falha ao enviar alerta de abertura', [
                    'league_id' => $league->id,
                    'slot_key' => $slotKey,
                    'reminder_id' => $reminder->id,
                    'email' => $reminder->email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        Log::info('[FantasyLeagueOpeningReminder] Processamento de alerta de abertura concluido', [
            'league_id' => $league->id,
            'slot_key' => $slotKey,
            'sent_count' => $sentCount,
            'storage' => $this->usesDatabaseStorage() ? 'database' : 'fallback',
        ]);

        return $sentCount;
    }

    public function usesDatabaseStorage(): bool
    {
        try {
            return Schema::hasTable('fantasy_league_opening_reminders');
        } catch (\Throwable $exception) {
            Log::warning('[FantasyLeagueOpeningReminder] Falha ao verificar tabela de lembretes', [
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function hasOpenLeagueForSlot(string $slotKey): bool
    {
        return FantasyLeague::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'finalized');
            })
            ->get()
            ->contains(function (FantasyLeague $league) use ($slotKey) {
                return $this->slotKeyForLeague($league) === $slotKey
                    && $this->isLeagueOpenForNotifications($league);
            });
    }

    private function isLeagueOpenForNotifications(FantasyLeague $league): bool
    {
        if (!(bool) ($league->is_active ?? false)) {
            return false;
        }

        if ((string) ($league->status ?? '') === 'finalized') {
            return false;
        }

        if ((bool) ($league->is_premium ?? false)) {
            return false;
        }

        return $league->isRegistrationOpen();
    }

    private function resolveReminderName(?User $user, ?string $name, string $email): string
    {
        $fromUser = trim((string) ($user?->fullname ?: $user?->firstname ?: $user?->username ?: ''));
        $fromInput = trim((string) $name);

        if ($fromUser !== '') {
            return $fromUser;
        }

        if ($fromInput !== '') {
            return $fromInput;
        }

        $emailName = trim((string) preg_replace('/@.*$/', '', $email));

        return $emailName !== '' ? $emailName : 'Competidor';
    }

    private function deliverEmail(FantasyLeagueOpeningReminder $reminder, string $subject, string $message): void
    {
        $email = trim((string) $reminder->email);
        if ($email === '') {
            throw new \RuntimeException('O lembrete não possui um e-mail válido para envio.');
        }

        $fromAddress = (string) (gs('email_from') ?: config('mail.from.address'));
        $fromName = (string) (gs('email_from_name') ?: gs('site_name') ?: config('mail.from.name'));

        if ($fromAddress === '') {
            throw new \RuntimeException('O endereço remetente de e-mail não está configurado.');
        }

        Mail::html($message, function ($mailMessage) use ($email, $subject, $fromAddress, $fromName, $reminder) {
            $mailMessage->to($email, $reminder->name ?: 'Competidor')
                ->subject($subject)
                ->from($fromAddress, $fromName);
        });
    }

    private function leagueOpenedEmailSubject(FantasyLeague $league, string $slotKey): string
    {
        $slotLabel = $this->slotLabel($slotKey);
        $leagueName = trim((string) (($league->name ?? null) ?: 'Bolão')); 

        return sprintf('%s liberado agora: monte seu time e entre no bolão %s', $slotLabel, $leagueName);
    }

    private function buildLeagueOpenedEmailPrizeSummary(FantasyLeague $league): array
    {
        $currentTeams = (int) $league->teams()->where('is_active', true)->count();
        $targetTeams = max((int) ($league->max_users ?? 0), $currentTeams);
        $paidPositions = $this->getLeagueOpenedEmailPaidPositions($league, $targetTeams);
        $displayPaidPositions = max($paidPositions, 3);
        $projectedPrizePool = $this->getLeagueOpenedEmailPrizePool($league, $targetTeams);
        $distribution = $this->getLeagueOpenedEmailPrizeDistribution($league, $displayPaidPositions);

        $topThree = [];
        for ($position = 1; $position <= 3; $position++) {
            $percent = (float) ($distribution[$position] ?? 0);
            $topThree[] = [
                'position' => $position,
                'percent' => $percent,
                'amount' => round($projectedPrizePool * ($percent / 100), 2),
            ];
        }

        return [
            'current_teams' => $currentTeams,
            'target_teams' => $targetTeams,
            'paid_positions' => $paidPositions,
            'display_paid_positions' => $displayPaidPositions,
            'projected_prize_pool' => $projectedPrizePool,
            'top_three' => $topThree,
        ];
    }

    private function getLeagueOpenedEmailPaidPositions(FantasyLeague $league, int $totalPlayers): int
    {
        if ($totalPlayers <= 0) {
            return 0;
        }

        $override = (int) ($league->paid_positions_override ?? 0);
        $maxUsers = (int) ($league->max_users ?? 0);

        if ($override > 0) {
            if ($maxUsers <= 0 || $totalPlayers >= $maxUsers) {
                return min($override, $totalPlayers);
            }
        }

        return max(1, (int) floor($totalPlayers * 10 / 100));
    }

    private function getLeagueOpenedEmailPrizePool(FantasyLeague $league, int $totalTeams): float
    {
        $configuredPrize = (float) ($league->total_prize ?? 0);
        if ($configuredPrize > 0) {
            return $configuredPrize;
        }

        $manualPrize = (float) ($league->manual_prize_pool ?? 0);
        if ($manualPrize > 0) {
            return $manualPrize;
        }

        if ((bool) $league->is_premium) {
            return 0.0;
        }

        $entryPrice = (float) ($league->price ?? 0);
        $houseCut = (float) ($league->house_cut_percent ?? 30);
        $baseTeamsForDisplay = max($totalTeams, (int) ($league->max_users ?? 0));
        $totalCollection = $baseTeamsForDisplay * $entryPrice;

        return max(0, $totalCollection * (1 - ($houseCut / 100)));
    }

    private function getLeagueOpenedEmailPrizeDistribution(FantasyLeague $league, int $paidPositions): array
    {
        if (!empty($league->prize_distribution)) {
            $distribution = is_string($league->prize_distribution)
                ? json_decode($league->prize_distribution, true)
                : $league->prize_distribution;

            if (is_array($distribution) && !empty($distribution)) {
                $raw = [];
                foreach ($distribution as $pos => $pct) {
                    $raw[(int) $pos] = (float) $pct;
                }
                ksort($raw);
                $normalized = $this->normalizeLeagueOpenedEmailDistribution($raw, $paidPositions);
                if (!empty($normalized)) {
                    return $normalized;
                }
            }
        }

        return $this->getLeagueOpenedEmailDefaultDistribution($paidPositions);
    }

    private function normalizeLeagueOpenedEmailDistribution(array $distribution, int $paidPositions): array
    {
        if ($paidPositions <= 0) {
            return [];
        }

        $normalized = [];
        foreach ($distribution as $position => $percent) {
            $position = (int) $position;
            $percent = (float) $percent;
            if ($position < 1 || $position > $paidPositions || $percent < 0) {
                continue;
            }
            $normalized[$position] = $percent;
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
            $normalized[$position] = round(($percent / $sum) * 100, 6);
        }

        $finalSum = array_sum($normalized);
        if (abs($finalSum - 100.0) > 0.0001 && isset($normalized[1])) {
            $normalized[1] = round($normalized[1] + (100.0 - $finalSum), 6);
        }

        return $normalized;
    }

    private function getLeagueOpenedEmailDefaultDistribution(int $paidPositions): array
    {
        if ($paidPositions <= 0) {
            return [];
        }

        if ($paidPositions === 1) {
            return [1 => 100.0];
        }

        if ($paidPositions === 2) {
            return [1 => 65.0, 2 => 35.0];
        }

        if ($paidPositions === 3) {
            return [1 => 50.0, 2 => 30.0, 3 => 20.0];
        }

        return [1 => 50.0, 2 => 30.0, 3 => 20.0];
    }

    private function subscribeUsingFallback(string $slotKey, string $email, string $displayName, ?User $user = null): FantasyLeagueOpeningReminder
    {
        $items = $this->loadFallbackItems();
        $matchIndex = null;

        foreach ($items as $index => $item) {
            if (($item['slot_key'] ?? null) === $slotKey && mb_strtolower((string) ($item['email'] ?? '')) === $email) {
                $matchIndex = $index;
                break;
            }
        }

        $payload = [
            'slot_key' => $slotKey,
            'user_id' => $user?->id,
            'email' => $email,
            'name' => $displayName,
            'opened_notification_sent_at' => null,
            'created_at' => $matchIndex !== null ? ($items[$matchIndex]['created_at'] ?? now()->toISOString()) : now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        if ($matchIndex !== null) {
            $items[$matchIndex] = $payload;
        } else {
            $items[] = $payload;
        }

        $this->saveFallbackItems($items);

        $reminder = new FantasyLeagueOpeningReminder($payload);
        if ($user) {
            $reminder->setRelation('user', $user);
        }

        return $reminder;
    }

    private function subscribedSlotsUsingFallback(?User $user, string $email): array
    {
        return collect($this->loadFallbackItems())
            ->filter(function ($item) use ($user, $email) {
                if (!empty($item['opened_notification_sent_at'])) {
                    return false;
                }

                $matchesUser = $user && (int) ($item['user_id'] ?? 0) === (int) $user->id;
                $matchesEmail = $email !== '' && mb_strtolower((string) ($item['email'] ?? '')) === $email;

                return $matchesUser || $matchesEmail;
            })
            ->pluck('slot_key')
            ->map(fn ($value) => (string) $value)
            ->filter(fn ($value) => $this->normalizeSlot($value) !== null)
            ->unique()
            ->values()
            ->all();
    }

    private function sendLeagueOpenedNotificationsUsingFallback(FantasyLeague $league, string $slotKey): int
    {
        $items = $this->loadFallbackItems();
        if (!$items) {
            return 0;
        }

        $userIds = collect($items)->pluck('user_id')->filter()->unique()->values();
        $users = $userIds->isNotEmpty()
            ? User::query()->whereIn('id', $userIds)->get()->keyBy('id')
            : collect();

        $sentCount = 0;

        foreach ($items as $index => $item) {
            if (($item['slot_key'] ?? null) !== $slotKey || !empty($item['opened_notification_sent_at'])) {
                continue;
            }

            $user = !empty($item['user_id']) ? $users->get((int) $item['user_id']) : null;
            $reminder = new FantasyLeagueOpeningReminder($item);
            if ($user) {
                $reminder->setRelation('user', $user);
            }

            try {
                $message = view('emails.fantasy.reminder-league-opened', [
                    'league' => $league,
                    'slotLabel' => $this->slotLabel($slotKey),
                    'emailPrizeSummary' => $this->buildLeagueOpenedEmailPrizeSummary($league),
                    'ctaUrl' => route('home'),
                ])->render();

                $subject = $this->leagueOpenedEmailSubject($league, $slotKey);
                $this->deliverEmail($reminder, $subject, $message);

                $items[$index]['opened_notification_sent_at'] = now()->toISOString();
                $items[$index]['updated_at'] = now()->toISOString();
                $sentCount++;
            } catch (\Throwable $exception) {
                Log::warning('[FantasyLeagueOpeningReminder] Falha ao enviar alerta de abertura (fallback)', [
                    'league_id' => $league->id,
                    'slot_key' => $slotKey,
                    'email' => $item['email'] ?? null,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->saveFallbackItems($items);

        return $sentCount;
    }

    private function fallbackFilePath(): string
    {
        return storage_path('app/fantasy_league_opening_reminders.json');
    }

    private function loadFallbackItems(): array
    {
        $path = $this->fallbackFilePath();
        if (!File::exists($path)) {
            return [];
        }

        try {
            $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            Log::warning('[FantasyLeagueOpeningReminder] Falha ao ler fallback local', [
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return [];
        }

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
    }

    private function saveFallbackItems(array $items): void
    {
        $path = $this->fallbackFilePath();
        $directory = dirname($path);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        File::put($path, json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}