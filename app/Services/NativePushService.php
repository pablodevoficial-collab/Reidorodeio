<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class NativePushService
{
    public function isConfigured(): bool
    {
        return count($this->configurationIssues()) === 0;
    }

    public function configurationIssues(): array
    {
        $issues = [];
        $serviceAccountPath = $this->serviceAccountPath();

        if (!file_exists($serviceAccountPath)) {
            $issues[] = 'Envie o arquivo service account do Firebase em App Control > Push do App.';
        }

        if (!$this->projectId()) {
            $issues[] = 'Preencha o projectId do Firebase em App Control > Push do App.';
        }

        return $issues;
    }

    public function sendToUser(int $userId, array $payload): array
    {
        $tokens = DeviceToken::query()
            ->where('user_id', $userId)
            ->where('is_app', true)
            ->pluck('token')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $this->sendToTokens($tokens, $payload);
    }

    public function sendToUserIds(iterable $userIds, array $payload): array
    {
        $normalizedIds = collect($userIds)
            ->filter(fn ($value) => !is_null($value))
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        if ($normalizedIds->isEmpty()) {
            return $this->emptyResult();
        }

        $tokens = DeviceToken::query()
            ->whereIn('user_id', $normalizedIds->all())
            ->where('is_app', true)
            ->pluck('token')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $this->sendToTokens($tokens, $payload);
    }

    public function sendToAll(array $payload): array
    {
        $tokens = DeviceToken::query()
            ->where('is_app', true)
            ->pluck('token')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $this->sendToTokens($tokens, $payload);
    }

    public function registerToken(int $userId, string $token, bool $isApp = true): DeviceToken
    {
        return DeviceToken::query()->updateOrCreate(
            ['token' => $token],
            [
                'user_id' => $userId,
                'is_app' => $isApp,
            ]
        );
    }

    public function unregisterToken(int $userId, string $token): void
    {
        DeviceToken::query()
            ->where('user_id', $userId)
            ->where('token', $token)
            ->delete();
    }

    public function publicClientConfig(): array
    {
        $firebase = gs('firebase_config');

        return [
            'apiKey' => $firebase->apiKey ?? null,
            'appId' => $firebase->appId ?? null,
            'messagingSenderId' => $firebase->messagingSenderId ?? null,
            'projectId' => $firebase->projectId ?? null,
            'storageBucket' => $firebase->storageBucket ?? null,
            'authDomain' => $firebase->authDomain ?? null,
            'measurementId' => $firebase->measurementId ?? null,
        ];
    }

    public function hasPublicClientConfig(): bool
    {
        $config = $this->publicClientConfig();

        return !empty($config['apiKey'])
            && !empty($config['appId'])
            && !empty($config['messagingSenderId'])
            && !empty($config['projectId']);
    }

    public function sendToTokens(array $tokens, array $payload): array
    {
        $tokens = collect($tokens)->filter()->unique()->values();

        if ($tokens->isEmpty()) {
            return $this->emptyResult();
        }

        $issues = $this->configurationIssues();
        if ($issues) {
            throw new RuntimeException(implode(' ', $issues));
        }

        $accessToken = $this->accessToken();
        $projectId = $this->projectId();
        $results = $this->emptyResult();

        foreach ($tokens as $token) {
            try {
                $response = Http::withToken($accessToken)
                    ->acceptJson()
                    ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                        'message' => [
                            'token' => $token,
                            'notification' => [
                                'title' => $payload['title'] ?? gs('site_name'),
                                'body' => $payload['body'] ?? '',
                                'image' => $payload['image'] ?? null,
                            ],
                            'data' => array_filter([
                                'click_action' => (string) ($payload['url'] ?? ''),
                                'app_click_action' => (string) ($payload['app_click_action'] ?? 'HOME'),
                                'source' => (string) ($payload['source'] ?? 'app_control'),
                            ], fn ($value) => $value !== ''),
                            'android' => [
                                'priority' => 'high',
                            ],
                        ],
                    ]);

                if ($response->successful()) {
                    $results['success']++;
                    continue;
                }

                $results['failed']++;
                $errorBody = $response->json();
                $message = data_get($errorBody, 'error.message', $response->body());
                $results['errors'][] = [
                    'token' => $token,
                    'error' => $message,
                ];

                if (str_contains((string) $message, 'UNREGISTERED')
                    || str_contains((string) $message, 'registration-token-not-registered')) {
                    DeviceToken::query()->where('token', $token)->delete();
                    $results['expired']++;
                }
            } catch (\Throwable $exception) {
                $results['failed']++;
                $results['errors'][] = [
                    'token' => $token,
                    'error' => $exception->getMessage(),
                ];

                Log::error('Erro ao enviar push nativo', [
                    'token' => $token,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $results;
    }

    private function accessToken(): string
    {
        $client = new \Google_Client();
        $client->setAuthConfig($this->serviceAccountPath());
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();

        $token = $client->getAccessToken();
        $accessToken = $token['access_token'] ?? null;

        if (!$accessToken) {
            throw new RuntimeException('Nao foi possivel obter access token do Firebase.');
        }

        return $accessToken;
    }

    private function projectId(): ?string
    {
        $projectId = gs('firebase_config')->projectId ?? null;
        if ($projectId) {
            return $projectId;
        }

        $jsonPath = $this->serviceAccountPath();
        if (!file_exists($jsonPath)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($jsonPath), true);
        return $decoded['project_id'] ?? null;
    }

    private function serviceAccountPath(): string
    {
        return getFilePath('pushConfig') . '/push_config.json';
    }

    private function emptyResult(): array
    {
        return [
            'success' => 0,
            'failed' => 0,
            'expired' => 0,
            'errors' => [],
        ];
    }
}
