<?php

namespace App\Services;

use App\Models\Competitor;
use App\Models\CompetitorFollowEvent;
use App\Models\CompetitorFollower;
use App\Models\User;
use App\Notify\Email;
use Illuminate\Support\Facades\Log;

class CompetitorFollowerService
{
    public function follow(User $user, Competitor $competitor): CompetitorFollower
    {
        return CompetitorFollower::firstOrCreate([
            'competitor_id' => $competitor->id,
            'user_id' => $user->id,
        ]);
    }

    public function unfollow(User $user, Competitor $competitor): void
    {
        CompetitorFollower::query()
            ->where('competitor_id', $competitor->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    public function isFollowing(?User $user, Competitor $competitor): bool
    {
        if (!$user) {
            return false;
        }

        return CompetitorFollower::query()
            ->where('competitor_id', $competitor->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function createEvent(Competitor $competitor, string $eventType, array $payload): ?CompetitorFollowEvent
    {
        $attributes = [
            'competitor_id' => $competitor->id,
            'source_key' => !empty($payload['source_key']) ? (string) $payload['source_key'] : null,
        ];

        $values = [
            'event_type' => $eventType,
            'title' => (string) ($payload['title'] ?? 'Nova movimentação do competidor'),
            'message' => (string) ($payload['message'] ?? 'Seu competidor teve uma atualização na Rei do Rodeio.'),
            'cta_label' => (string) ($payload['cta_label'] ?? 'Ver ficha completa'),
            'cta_url' => (string) ($payload['cta_url'] ?? route('hub.stats', ['competitor' => $competitor->id])),
            'metadata' => $payload['metadata'] ?? null,
            'rodeio_id' => $payload['rodeio_id'] ?? null,
            'modalidade_id' => $payload['modalidade_id'] ?? null,
            'fantasy_league_id' => $payload['fantasy_league_id'] ?? null,
            'scoring_log_id' => $payload['scoring_log_id'] ?? null,
        ];

        $event = $attributes['source_key']
            ? CompetitorFollowEvent::firstOrCreate($attributes, $values)
            : CompetitorFollowEvent::create(array_merge($attributes, $values));

        if (!$event->wasRecentlyCreated) {
            return null;
        }

        $this->notifyFollowers($event);

        return $event;
    }

    public function notifyFollowers(CompetitorFollowEvent $event): void
    {
        $event->loadMissing('competitor', 'rodeio', 'modalidade');

        $followers = CompetitorFollower::query()
            ->with('user:id,firstname,lastname,username,email')
            ->where('competitor_id', $event->competitor_id)
            ->get();

        foreach ($followers as $follower) {
            $user = $follower->user;
            if (!$user || !$user->email) {
                continue;
            }

            try {
                $contextBits = array_filter([
                    $event->modalidade?->nome,
                    $event->rodeio?->name,
                ]);

                $contextLine = !empty($contextBits)
                    ? '<p style="margin:0 0 12px;color:#8ca2c8;font-size:14px;"><strong>Contexto:</strong> ' . e(implode(' • ', $contextBits)) . '</p>'
                    : '';

                $ctaUrl = (string) ($event->cta_url ?: route('hub.stats', ['competitor' => $event->competitor_id]));
                $ctaLabel = (string) ($event->cta_label ?: 'Ver ficha completa');

                $message = ''
                    . '<h2 style="margin:0 0 14px;color:#ffffff;font-size:24px;">' . e($event->title) . '</h2>'
                    . '<p style="margin:0 0 14px;color:#d6e2f5;font-size:15px;line-height:1.7;">' . e($event->message) . '</p>'
                    . $contextLine
                    . '<p style="margin:18px 0 0;">'
                    . '<a href="' . e($ctaUrl) . '" style="display:inline-block;padding:12px 18px;border-radius:12px;background:#f97316;color:#ffffff;text-decoration:none;font-weight:700;">'
                    . e($ctaLabel)
                    . '</a>'
                    . '</p>';

                $mail = new Email();
                $mail->templateName = null;
                $mail->user = $user;
                $mail->userColumn = $user->getForeignKey();
                $mail->createLog = true;
                $mail->subject = (string) $event->title;
                $mail->message = $message;
                $mail->send();
            } catch (\Throwable $exception) {
                Log::warning('[CompetitorFollower] Falha ao enviar e-mail', [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}
