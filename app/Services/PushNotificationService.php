<?php

namespace App\Services;

use App\Models\PushSubscription;
use RuntimeException;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $generalVapid = gs('vapid_config');
        $publicKey = $generalVapid->public_key ?? config('services.vapid.public_key');
        $privateKey = $generalVapid->private_key ?? config('services.vapid.private_key');
        $subject = $generalVapid->subject ?? config('app.url');

        if (empty($publicKey) || empty($privateKey)) {
            throw new RuntimeException('As chaves VAPID do push não estão configuradas. Vá em Notificações > Config. Push.');
        }

        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => $subject,
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);
    }

    /**
     * Subscrever um usuário ao push
     */
    public function subscribe(array $subscriptionData, ?int $userId = null, ?string $userAgent = null, ?string $ipAddress = null): PushSubscription
    {
        $endpoint = $subscriptionData['endpoint'];
        $keys = $subscriptionData['keys'] ?? [];

        // Verificar se já existe
        $subscription = PushSubscription::where('endpoint', $endpoint)->first();

        if ($subscription) {
            // Atualizar existente
            $subscription->update([
                'user_id' => $userId ?? $subscription->user_id,
                'public_key' => $keys['p256dh'] ?? $subscription->public_key,
                'auth_token' => $keys['auth'] ?? $subscription->auth_token,
                'content_encoding' => $subscriptionData['contentEncoding'] ?? $subscription->content_encoding,
                'user_agent' => $userAgent ?? $subscription->user_agent,
                'ip_address' => $ipAddress ?? $subscription->ip_address,
                'is_active' => true,
                'last_used_at' => now(),
            ]);
        } else {
            // Criar nova
            $subscription = PushSubscription::create([
                'user_id' => $userId,
                'endpoint' => $endpoint,
                'public_key' => $keys['p256dh'] ?? null,
                'auth_token' => $keys['auth'] ?? null,
                'content_encoding' => $subscriptionData['contentEncoding'] ?? 'aes128gcm',
                'user_agent' => $userAgent,
                'ip_address' => $ipAddress,
                'is_active' => true,
                'last_used_at' => now(),
            ]);
        }

        return $subscription;
    }

    /**
     * Enviar notificação para um usuário específico
     */
    public function sendToUser(int $userId, array $payload): array
    {
        $subscriptions = PushSubscription::where('user_id', $userId)
            ->active()
            ->get();

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Enviar notificação para todos os usuários
     */
    public function sendToAll(array $payload): array
    {
        $subscriptions = PushSubscription::active()->get();

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Enviar notificação para múltiplas subscriptions
     */
    public function sendToSubscriptions($subscriptions, array $payload): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'expired' => 0,
            'errors' => [],
        ];

        foreach ($subscriptions as $pushSubscription) {
            try {
                $subscription = Subscription::create($pushSubscription->toWebPushFormat());
                
                $this->webPush->queueNotification(
                    $subscription,
                    json_encode($payload)
                );

                $pushSubscription->markAsUsed();
                $results['success']++;
            } catch (\Exception $e) {
                Log::error('Erro ao enfileirar push notification', [
                    'subscription_id' => $pushSubscription->id,
                    'error' => $e->getMessage(),
                ]);
                $results['errors'][] = [
                    'subscription_id' => $pushSubscription->id,
                    'error' => $e->getMessage(),
                ];
                $results['failed']++;
            }
        }

        // Enviar todas as notificações enfileiradas
        try {
            foreach ($this->webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();

                if (!$report->isSuccess()) {
                    // Subscription expirada ou inválida
                    if ($report->isSubscriptionExpired()) {
                        $pushSubscription = PushSubscription::where('endpoint', $endpoint)->first();
                        if ($pushSubscription) {
                            $pushSubscription->deactivate();
                            $results['expired']++;
                        }
                    }

                    Log::warning('Push notification falhou', [
                        'endpoint' => $endpoint,
                        'reason' => $report->getReason(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar push notifications', [
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * Desinscrever um endpoint
     */
    public function unsubscribe(string $endpoint): bool
    {
        $subscription = PushSubscription::where('endpoint', $endpoint)->first();

        if ($subscription) {
            $subscription->deactivate();
            return true;
        }

        return false;
    }

    /**
     * Limpar subscriptions expiradas
     */
    public function cleanExpired(): int
    {
        return PushSubscription::where('is_active', false)
            ->where('updated_at', '<', now()->subDays(30))
            ->delete();
    }
}
