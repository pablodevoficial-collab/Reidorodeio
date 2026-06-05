<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSocialAccount;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class UserSocialAccountService
{
    private const LINK_TTL_MINUTES = 10;
    private ?bool $socialAccountsTableExists = null;

    /**
     * @var array<int, string>
     */
    private array $supportedProviders = ['google', 'facebook', 'apple'];

    public function normalizeProvider(?string $provider): ?string
    {
        $provider = Str::lower(trim((string) $provider));

        return in_array($provider, $this->supportedProviders, true)
            ? $provider
            : null;
    }

    /**
     * @return array<string, bool>
     */
    public function connectedProviders(User $user): array
    {
        $providers = array_fill_keys($this->supportedProviders, false);

        if ($this->hasSocialAccountsTable()) {
            foreach ($user->socialAccounts()->pluck('provider')->all() as $provider) {
                if (array_key_exists($provider, $providers)) {
                    $providers[$provider] = true;
                }
            }
        }

        $legacyProvider = $this->normalizeProvider((string) $user->provider);
        if ($legacyProvider !== null) {
            $providers[$legacyProvider] = true;
        }

        return $providers;
    }

    public function findUserByProvider(string $provider, string $providerId): ?User
    {
        $provider = $this->normalizeProvider($provider);
        $providerId = trim($providerId);

        if ($provider === null || $providerId === '') {
            return null;
        }

        if ($this->hasSocialAccountsTable()) {
            $account = UserSocialAccount::query()
                ->with('user')
                ->where('provider', $provider)
                ->where('provider_id', $providerId)
                ->first();

            if ($account?->user) {
                return $account->user;
            }
        }

        return User::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();
    }

    /**
     * @param array<string, mixed> $socialData
     */
    public function syncAccount(User $user, string $provider, array $socialData): UserSocialAccount
    {
        $provider = $this->normalizeProvider($provider);
        $providerId = trim((string) ($socialData['provider_id'] ?? ''));

        if ($provider === null || $providerId === '') {
            throw new RuntimeException('Dados inválidos para conexão social.');
        }

        $existingUser = $this->findUserByProvider($provider, $providerId);
        if ($existingUser && (int) $existingUser->id !== (int) $user->id) {
            throw new RuntimeException('Essa conta social já está vinculada a outro usuário.');
        }

        $name = trim((string) ($socialData['name'] ?? ''));
        if ($name === '') {
            $name = trim(
                trim((string) ($socialData['first_name'] ?? '')) . ' ' .
                trim((string) ($socialData['last_name'] ?? ''))
            );
        }

        $email = strtolower(trim((string) ($socialData['email'] ?? '')));

        if ($this->hasSocialAccountsTable()) {
            $account = UserSocialAccount::query()->firstOrNew([
                'provider' => $provider,
                'provider_id' => $providerId,
            ]);

            $account->user_id = $user->id;
            $account->provider_email = $email !== '' ? Str::limit($email, 191, '') : null;
            $account->provider_name = $name !== '' ? Str::limit($name, 191, '') : null;
            $account->connected_at = now();
            $account->save();
        } else {
            $account = new UserSocialAccount([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $providerId,
                'provider_email' => $email !== '' ? Str::limit($email, 191, '') : null,
                'provider_name' => $name !== '' ? Str::limit($name, 191, '') : null,
                'connected_at' => now(),
            ]);
        }

        $needsUserSave = false;
        if ((string) $user->provider !== $provider) {
            $user->provider = $provider;
            $needsUserSave = true;
        }
        if ((string) $user->provider_id !== $providerId) {
            $user->provider_id = $providerId;
            $needsUserSave = true;
        }

        if ($needsUserSave) {
            $user->save();
        }

        return $account;
    }

    public function ensureLegacyAccountSynced(User $user): void
    {
        if (!$this->hasSocialAccountsTable()) {
            return;
        }

        $provider = $this->normalizeProvider((string) $user->provider);
        $providerId = trim((string) $user->provider_id);

        if ($provider === null || $providerId === '') {
            return;
        }

        $exists = UserSocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->exists();

        if ($exists) {
            return;
        }

        $this->syncAccount($user, $provider, [
            'provider_id' => $providerId,
            'email' => $user->email,
            'name' => trim((string) $user->fullname),
            'first_name' => $user->firstname,
            'last_name' => $user->lastname,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function issueLinkToken(User $user, string $provider, string $platform = 'android'): array
    {
        $provider = $this->normalizeProvider($provider);
        if ($provider === null) {
            throw new RuntimeException('Provedor social inválido.');
        }

        $token = Str::random(64);
        $expiresAt = now()->addMinutes(self::LINK_TTL_MINUTES);

        Cache::put(
            $this->cacheKey($token),
            [
                'user_id' => $user->id,
                'provider' => $provider,
                'platform' => trim($platform) !== '' ? trim($platform) : 'android',
                'status' => 'pending',
            ],
            $expiresAt
        );

        return [
            'token' => $token,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    /**
     * @param array<string, mixed> $socialData
     */
    public function completeMobileLink(string $token, string $provider, array $socialData): User
    {
        $payload = $this->payload($token);
        $provider = $this->normalizeProvider($provider);

        if ($payload === null || $provider === null || ($payload['provider'] ?? null) !== $provider) {
            throw new RuntimeException('Solicitação de conexão social inválida ou expirada.');
        }

        /** @var User|null $user */
        $user = User::query()->find((int) ($payload['user_id'] ?? 0));
        if (!$user) {
            throw new RuntimeException('Usuário da conexão social não encontrado.');
        }

        $this->syncAccount($user, $provider, $socialData);

        $payload['status'] = 'success';
        Cache::put($this->cacheKey($token), $payload, now()->addMinutes(self::LINK_TTL_MINUTES));

        return $user;
    }

    public function isSuccessfulMobileLink(string $token, string $provider): bool
    {
        $payload = $this->payload($token);
        $provider = $this->normalizeProvider($provider);

        return $payload !== null
            && $provider !== null
            && ($payload['provider'] ?? null) === $provider
            && ($payload['status'] ?? null) === 'success';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function payload(string $token): ?array
    {
        $payload = Cache::get($this->cacheKey($token));

        return is_array($payload) ? $payload : null;
    }

    private function cacheKey(string $token): string
    {
        return 'mobile_social_link:' . trim($token);
    }

    private function hasSocialAccountsTable(): bool
    {
        if ($this->socialAccountsTableExists !== null) {
            return $this->socialAccountsTableExists;
        }

        try {
            $this->socialAccountsTableExists = Schema::hasTable('user_social_accounts');
        } catch (\Throwable) {
            $this->socialAccountsTableExists = false;
        }

        return $this->socialAccountsTableExists;
    }
}
