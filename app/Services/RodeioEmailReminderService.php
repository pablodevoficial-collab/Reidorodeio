<?php

namespace App\Services;

use App\Models\Rodeio;
use App\Models\RodeioEmailReminder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class RodeioEmailReminderService
{
    private const LIVE_STATUSES = [
        'ao_vivo',
        'pausado',
        'classificatoria',
        'em_apuracao',
        'inicio_finais',
        'divisao_finalizada',
    ];

    public function subscribe(Rodeio $rodeio, string $emailAddress, ?User $user = null, ?string $name = null): RodeioEmailReminder
    {
        $email = mb_strtolower(trim($emailAddress));
        $displayName = $this->resolveReminderName($user, $name, $email);

        if (!$this->usesDatabaseStorage()) {
            $reminder = $this->subscribeUsingFallback($rodeio, $email, $displayName, $user);
        } else {
            $reminder = RodeioEmailReminder::query()->firstOrNew([
                'rodeio_id' => $rodeio->id,
                'email' => $email,
            ]);

            if ($user && !$reminder->user_id) {
                $reminder->user_id = $user->id;
            }

            $reminder->name = $displayName;
            $reminder->save();
        }

        try {
            $this->sendConfirmation($reminder);
        } catch (\Throwable $exception) {
            Log::warning('[RodeioReminder] Falha ao enviar confirmação após ativar alerta', [
                'rodeio_id' => $rodeio->id,
                'user_id' => $user?->id,
                'email' => $email,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return $reminder->fresh(['rodeio', 'user']) ?? $reminder;
    }

    public function sendConfirmation(RodeioEmailReminder $reminder): void
    {
        $reminder->loadMissing('rodeio', 'user');

        $rodeio = $reminder->rodeio;
        if (!$rodeio) {
            return;
        }

        $startAt = $this->parseEventDate($rodeio->start ?? null);
        $message = view('emails.rodeios.reminder-confirmation', [
            'reminder' => $reminder,
            'rodeio' => $rodeio,
            'startAt' => $startAt,
            'ctaUrl' => route('home'),
        ])->render();

        $subject = sprintf('Alerta ativado para %s | Rei do Rodeio', $this->rodeioTitle($rodeio));
        $this->deliverEmail($reminder, $subject, $message);

        $this->markConfirmationSent($reminder);
    }

    public function sendLiveStartedNotifications(): int
    {
        if (!$this->usesDatabaseStorage()) {
            return $this->sendLiveStartedNotificationsUsingFallback();
        }

        $reminders = RodeioEmailReminder::query()
            ->with(['rodeio', 'user'])
            ->whereNull('live_notification_sent_at')
            ->whereHas('rodeio', function ($query) {
                $query->whereIn('status_transmissao', self::LIVE_STATUSES);
            })
            ->get();

        $sentCount = 0;

        foreach ($reminders as $reminder) {
            $rodeio = $reminder->rodeio;
            if (!$rodeio) {
                continue;
            }

            try {
                $message = view('emails.rodeios.reminder-live-started', [
                    'reminder' => $reminder,
                    'rodeio' => $rodeio,
                    'ctaUrl' => route('home'),
                ])->render();

                $subject = sprintf('%s começou agora | Rei do Rodeio', $this->rodeioTitle($rodeio));
                $this->deliverEmail($reminder, $subject, $message);

                $reminder->forceFill([
                    'live_notification_sent_at' => now(),
                ])->save();

                $sentCount++;
            } catch (\Throwable $exception) {
                Log::warning('[RodeioReminder] Falha ao enviar alerta de início', [
                    'rodeio_id' => $rodeio->id,
                    'reminder_id' => $reminder->id,
                    'email' => $reminder->email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $sentCount;
    }

    public function canSubscribe(Rodeio $rodeio): bool
    {
        $status = (string) ($rodeio->status_transmissao ?? '');
        if (in_array($status, self::LIVE_STATUSES, true)) {
            return false;
        }

        $startAt = $this->parseEventDate($rodeio->start ?? null);

        return !$startAt || $startAt->isFuture();
    }

    public function subscribedRodeioIdsFor(?User $user = null, ?string $emailAddress = null): array
    {
        $email = mb_strtolower(trim((string) $emailAddress));

        if (!$user && $email === '') {
            return [];
        }

        if (!$this->usesDatabaseStorage()) {
            return $this->subscribedRodeioIdsUsingFallback($user, $email);
        }

        $query = RodeioEmailReminder::query();

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

        return $query
            ->pluck('rodeio_id')
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function usesDatabaseStorage(): bool
    {
        try {
            return Schema::hasTable('rodeio_email_reminders');
        } catch (\Throwable $exception) {
            Log::warning('[RodeioReminder] Falha ao verificar tabela de lembretes, usando fallback', [
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function deliverEmail(RodeioEmailReminder $reminder, string $subject, string $message): void
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
            $mailMessage->to($email, $reminder->name ?: 'Fã do rodeio')
                ->subject($subject)
                ->from($fromAddress, $fromName);
        });
    }

    private function resolveReminderName(?User $user, ?string $name, string $email): string
    {
        $candidate = trim((string) ($name ?? ''));
        if ($candidate !== '') {
            return $candidate;
        }

        if ($user) {
            $fullName = trim((string) ($user->firstname . ' ' . $user->lastname));
            if ($fullName !== '') {
                return $fullName;
            }

            if (!empty($user->username)) {
                return (string) $user->username;
            }
        }

        return trim((string) strtok($email, '@')) ?: 'Fã do rodeio';
    }

    private function parseEventDate($value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function rodeioTitle(Rodeio $rodeio): string
    {
        return trim((string) (($rodeio->nome ?? $rodeio->titulo ?? $rodeio->name ?? null) ?: 'Próximo rodeio'));
    }

    private function subscribeUsingFallback(Rodeio $rodeio, string $email, string $displayName, ?User $user = null): RodeioEmailReminder
    {
        $items = $this->loadFallbackItems();
        $matchIndex = null;

        foreach ($items as $index => $item) {
            if ((int) ($item['rodeio_id'] ?? 0) === (int) $rodeio->id && mb_strtolower((string) ($item['email'] ?? '')) === $email) {
                $matchIndex = $index;
                break;
            }
        }

        $payload = [
            'rodeio_id' => $rodeio->id,
            'user_id' => $user?->id,
            'email' => $email,
            'name' => $displayName,
            'confirmation_sent_at' => $matchIndex !== null ? ($items[$matchIndex]['confirmation_sent_at'] ?? null) : null,
            'live_notification_sent_at' => $matchIndex !== null ? ($items[$matchIndex]['live_notification_sent_at'] ?? null) : null,
            'created_at' => $matchIndex !== null ? ($items[$matchIndex]['created_at'] ?? now()->toISOString()) : now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        if ($matchIndex !== null) {
            $items[$matchIndex] = $payload;
        } else {
            $items[] = $payload;
        }

        $this->saveFallbackItems($items);

        $reminder = new RodeioEmailReminder($payload);
        $reminder->setRelation('rodeio', $rodeio);
        if ($user) {
            $reminder->setRelation('user', $user);
        }

        return $reminder;
    }

    private function sendLiveStartedNotificationsUsingFallback(): int
    {
        $items = $this->loadFallbackItems();
        if (!$items) {
            return 0;
        }

        $rodeios = Rodeio::query()
            ->whereIn('status_transmissao', self::LIVE_STATUSES)
            ->get()
            ->keyBy('id');

        $userIds = collect($items)->pluck('user_id')->filter()->unique()->values();
        $users = $userIds->isNotEmpty()
            ? User::query()->whereIn('id', $userIds)->get()->keyBy('id')
            : collect();

        $sentCount = 0;

        foreach ($items as $index => $item) {
            if (!empty($item['live_notification_sent_at'])) {
                continue;
            }

            $rodeioId = (int) ($item['rodeio_id'] ?? 0);
            $rodeio = $rodeios->get($rodeioId);

            if (!$rodeio) {
                continue;
            }

            $user = !empty($item['user_id']) ? $users->get((int) $item['user_id']) : null;
            $reminder = new RodeioEmailReminder($item);
            $reminder->setRelation('rodeio', $rodeio);
            if ($user) {
                $reminder->setRelation('user', $user);
            }

            try {
                $message = view('emails.rodeios.reminder-live-started', [
                    'reminder' => $reminder,
                    'rodeio' => $rodeio,
                    'ctaUrl' => route('home'),
                ])->render();

                $subject = sprintf('%s começou agora | Rei do Rodeio', $this->rodeioTitle($rodeio));
                $this->deliverEmail($reminder, $subject, $message);

                $items[$index]['live_notification_sent_at'] = now()->toISOString();
                $items[$index]['updated_at'] = now()->toISOString();
                $sentCount++;
            } catch (\Throwable $exception) {
                Log::warning('[RodeioReminder] Falha ao enviar alerta de início (fallback)', [
                    'rodeio_id' => $rodeio->id,
                    'email' => $item['email'] ?? null,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->saveFallbackItems($items);

        return $sentCount;
    }

    private function subscribedRodeioIdsUsingFallback(?User $user, string $email): array
    {
        return collect($this->loadFallbackItems())
            ->filter(function ($item) use ($user, $email) {
                $matchesUser = $user && (int) ($item['user_id'] ?? 0) === (int) $user->id;
                $matchesEmail = $email !== '' && mb_strtolower((string) ($item['email'] ?? '')) === $email;

                return $matchesUser || $matchesEmail;
            })
            ->pluck('rodeio_id')
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function markConfirmationSent(RodeioEmailReminder $reminder): void
    {
        if ($reminder->confirmation_sent_at) {
            return;
        }

        $timestamp = now();

        if ($this->usesDatabaseStorage() && $reminder->exists) {
            $reminder->forceFill([
                'confirmation_sent_at' => $timestamp,
            ])->save();

            return;
        }

        $items = $this->loadFallbackItems();
        foreach ($items as $index => $item) {
            if ((int) ($item['rodeio_id'] ?? 0) === (int) $reminder->rodeio_id
                && mb_strtolower((string) ($item['email'] ?? '')) === mb_strtolower((string) $reminder->email)) {
                $items[$index]['confirmation_sent_at'] = $timestamp->toISOString();
                $items[$index]['updated_at'] = $timestamp->toISOString();
                $this->saveFallbackItems($items);
                break;
            }
        }
    }

    private function fallbackFilePath(): string
    {
        return storage_path('app/rodeio_email_reminders.json');
    }

    private function loadFallbackItems(): array
    {
        $path = $this->fallbackFilePath();
        if (!File::exists($path)) {
            return [];
        }

        try {
            $decoded = json_decode((string) File::get($path), true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
        } catch (\Throwable $exception) {
            Log::warning('[RodeioReminder] Falha ao ler fallback de lembretes', [
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return [];
        }
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
