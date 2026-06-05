<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLogin;
use App\Services\UserSocialAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    /**
     * @var array<int, string>
     */
    private array $supportedProviders = ['google', 'facebook', 'apple'];

    public function __construct(
        private readonly UserSocialAccountService $socialAccountService
    ) {
    }

    public function redirect(Request $request, string $provider): RedirectResponse
    {
        $provider = $this->normalizeProvider($provider);
        if (!$provider) {
            return redirect()->route('home')->withErrors(['social' => 'Provedor de login social inválido.']);
        }

        $this->rememberReturnUrl($request);
        $this->rememberMobileLinkContext($request, $provider);

        try {
            if ($provider === 'apple') {
                return $this->redirectToApple();
            }

            $this->configureOAuthProvider($provider);

            $driver = Socialite::driver($provider);
            if (in_array($provider, ['google', 'facebook'], true)) {
                $driver = $driver->scopes(['email']);
            }

            return $driver->redirect();
        } catch (\Throwable $e) {
            report($e);
            return $this->failRedirect('Login social indisponível. Configure as credenciais do provedor no painel admin.');
        }
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $provider = $this->normalizeProvider($provider);
        if (!$provider) {
            return redirect()->route('home')->withErrors(['social' => 'Provedor de login social inválido.']);
        }

        if ($request->filled('error')) {
            return $this->failRedirect('Login social cancelado pelo usuário.');
        }

        try {
            if ($provider === 'apple') {
                $socialData = $this->handleAppleCallback($request);
            } else {
                $this->configureOAuthProvider($provider);

                try {
                    $socialUser = Socialite::driver($provider)->user();
                } catch (\Throwable $e) {
                    // Fallback for embedded webviews where state validation can fail.
                    $socialUser = Socialite::driver($provider)->stateless()->user();
                }

                $raw = is_array($socialUser->user ?? null) ? $socialUser->user : [];
                $socialData = [
                    'provider_id' => (string) $socialUser->getId(),
                    'email' => $socialUser->getEmail(),
                    'name' => $socialUser->getName(),
                    'first_name' => $raw['given_name'] ?? ($raw['first_name'] ?? null),
                    'last_name' => $raw['family_name'] ?? ($raw['last_name'] ?? null),
                ];
            }

            if (empty($socialData['provider_id'])) {
                return $this->failRedirect('Não foi possível identificar sua conta social.');
            }

            $mobileLinkToken = $this->consumeMobileLinkToken();
            if ($mobileLinkToken !== null) {
                $this->socialAccountService->completeMobileLink($mobileLinkToken, $provider, $socialData);
                Auth::logout();

                return redirect()->to($this->consumeReturnUrl());
            }

            $user = $this->findOrCreateSocialUser($provider, $socialData);
            $this->socialAccountService->syncAccount($user, $provider, $socialData);

            Auth::login($user, true);
            $user->current_session_id = session()->getId();
            $user->save();

            $this->logUserLogin($user, $request);

            return redirect()->to($this->consumeReturnUrl());
        } catch (\Throwable $e) {
            return $this->failRedirect('Falha no login social. Verifique as credenciais do provedor no painel admin.');
        }
    }

    private function normalizeProvider(string $provider): ?string
    {
        $provider = Str::lower(trim($provider));
        return in_array($provider, $this->supportedProviders, true) ? $provider : null;
    }

    private function configureOAuthProvider(string $provider): void
    {
        $credentials = $this->resolveCredentials($provider);

        if (empty($credentials['client_id']) || empty($credentials['client_secret'])) {
            throw new \RuntimeException("Credenciais ausentes para provider [{$provider}]");
        }

        Config::set("services.{$provider}", [
            'client_id' => $credentials['client_id'],
            'client_secret' => $credentials['client_secret'],
            'redirect' => route('user.social.callback', $provider),
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function resolveCredentials(string $provider): array
    {
        $fromSettings = null;
        try {
            $fromSettings = data_get(gs('socialite_credentials'), $provider);
        } catch (\Throwable $e) {
            $fromSettings = null;
        }

        $enabled = true;
        if (is_object($fromSettings) && property_exists($fromSettings, 'status')) {
            $enabled = (int) $fromSettings->status === 1;
        } elseif (is_array($fromSettings) && array_key_exists('status', $fromSettings)) {
            $enabled = (int) $fromSettings['status'] === 1;
        }

        if (!$enabled) {
            throw new \RuntimeException("Provider [{$provider}] desativado.");
        }

        $clientId = is_object($fromSettings)
            ? ($fromSettings->client_id ?? null)
            : (is_array($fromSettings) ? ($fromSettings['client_id'] ?? null) : null);

        $clientSecret = is_object($fromSettings)
            ? ($fromSettings->client_secret ?? null)
            : (is_array($fromSettings) ? ($fromSettings['client_secret'] ?? null) : null);

        if (!$clientId) {
            $clientId = config("services.{$provider}.client_id");
        }
        if (!$clientSecret) {
            $clientSecret = config("services.{$provider}.client_secret");
        }

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];
    }

    private function rememberReturnUrl(Request $request): void
    {
        $candidate = trim((string) $request->query('return_url', ''));
        if ($candidate !== '' && $this->isSafeReturnUrl($candidate)) {
            session()->put('social_login_return_url', $this->appendAppContextToUrl($candidate, $request));
            return;
        }

        $fallback = (string) url()->previous();
        if ($this->isSafeReturnUrl($fallback)) {
            session()->put('social_login_return_url', $this->appendAppContextToUrl($fallback, $request));
            return;
        }

        session()->put('social_login_return_url', $this->appendAppContextToUrl(route('home'), $request));
    }

    private function rememberMobileLinkContext(Request $request, string $provider): void
    {
        $mode = trim((string) $request->query('mode', ''));
        $linkToken = trim((string) $request->query('link_token', ''));

        if ($mode === 'link' && $linkToken !== '') {
            session()->put('mobile_social_link', [
                'provider' => $provider,
                'link_token' => $linkToken,
            ]);
            return;
        }

        session()->forget('mobile_social_link');
    }

    private function consumeReturnUrl(): string
    {
        $url = (string) session()->pull('social_login_return_url', '');
        if ($url !== '' && $this->isSafeReturnUrl($url)) {
            return $url;
        }

        return route('home');
    }

    private function consumeMobileLinkToken(): ?string
    {
        $payload = session()->pull('mobile_social_link');
        if (!is_array($payload)) {
            return null;
        }

        $provider = $this->normalizeProvider((string) ($payload['provider'] ?? ''));
        $token = trim((string) ($payload['link_token'] ?? ''));

        if ($provider === null || $token === '') {
            return null;
        }

        return $token;
    }

    private function isSafeReturnUrl(string $url): bool
    {
        $parts = parse_url($url);
        if ($parts === false) {
            return false;
        }

        if (isset($parts['scheme']) && !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        if (isset($parts['host']) && strcasecmp($parts['host'], request()->getHost()) !== 0) {
            return false;
        }

        return true;
    }

    private function appendAppContextToUrl(string $url, Request $request): string
    {
        $app = trim((string) $request->query('app', ''));
        if ($app === '' && trim((string) $request->query('source', '')) === 'app') {
            $app = '1';
        }

        return $this->appendQueryParamsIfMissing($url, [
            'tab' => trim((string) $request->query('tab', '')),
            'app' => $app,
            'platform' => trim((string) $request->query('platform', '')),
        ]);
    }

    /**
     * @param array<string, string> $params
     */
    private function appendQueryParamsIfMissing(string $url, array $params): string
    {
        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        $query = [];
        if (!empty($parts['query'])) {
            parse_str((string) $parts['query'], $query);
        }

        foreach ($params as $key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }

            if (!array_key_exists($key, $query)) {
                $query[$key] = $value;
            }
        }

        $parts['query'] = http_build_query($query);
        return $this->buildUrlFromParts($parts);
    }

    /**
     * @param array<string, mixed> $parts
     */
    private function buildUrlFromParts(array $parts): string
    {
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $user = isset($parts['user']) ? (string) $parts['user'] : '';
        $pass = isset($parts['pass']) ? ':' . (string) $parts['pass'] : '';
        $auth = $user !== '' ? $user . $pass . '@' : '';
        $host = isset($parts['host']) ? (string) $parts['host'] : '';
        $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';
        $path = isset($parts['path']) ? (string) $parts['path'] : '';
        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . (string) $parts['query'] : '';
        $fragment = isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . (string) $parts['fragment'] : '';

        return $scheme . $auth . $host . $port . $path . $query . $fragment;
    }

    private function redirectToApple(): RedirectResponse
    {
        $credentials = $this->resolveCredentials('apple');
        if (empty($credentials['client_id']) || empty($credentials['client_secret'])) {
            throw new \RuntimeException('Credenciais da Apple não configuradas.');
        }

        $state = Str::random(40);
        session()->put('apple_oauth_state', $state);

        $query = http_build_query([
            'response_type' => 'code',
            'response_mode' => 'form_post',
            'client_id' => $credentials['client_id'],
            'redirect_uri' => route('user.social.callback', 'apple'),
            'scope' => 'name email',
            'state' => $state,
        ]);

        return redirect()->away('https://appleid.apple.com/auth/authorize?' . $query);
    }

    /**
     * @return array<string, string|null>
     */
    private function handleAppleCallback(Request $request): array
    {
        $state = (string) $request->input('state', '');
        $expected = (string) session()->pull('apple_oauth_state', '');
        if ($state === '' || $expected === '' || !hash_equals($expected, $state)) {
            throw new \RuntimeException('State inválido no callback da Apple.');
        }

        $code = (string) $request->input('code', '');
        if ($code === '') {
            throw new \RuntimeException('Código de autorização Apple ausente.');
        }

        $credentials = $this->resolveCredentials('apple');

        $response = Http::asForm()->post('https://appleid.apple.com/auth/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => route('user.social.callback', 'apple'),
            'client_id' => $credentials['client_id'],
            'client_secret' => $credentials['client_secret'],
        ]);

        if (!$response->ok()) {
            throw new \RuntimeException('Falha ao trocar token com a Apple.');
        }

        $tokenData = $response->json();
        $idToken = (string) ($tokenData['id_token'] ?? '');
        $claims = $this->decodeJwtPayload($idToken);

        $namePayload = [];
        $rawUser = $request->input('user');
        if (is_string($rawUser) && $rawUser !== '') {
            $decoded = json_decode($rawUser, true);
            if (is_array($decoded)) {
                $namePayload = $decoded;
            }
        } elseif (is_array($rawUser)) {
            $namePayload = $rawUser;
        }

        $firstName = data_get($namePayload, 'name.firstName');
        $lastName = data_get($namePayload, 'name.lastName');

        return [
            'provider_id' => (string) ($claims['sub'] ?? ''),
            'email' => $claims['email'] ?? null,
            'name' => trim((string) ($firstName . ' ' . $lastName)),
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJwtPayload(string $jwt): array
    {
        if ($jwt === '' || substr_count($jwt, '.') < 2) {
            return [];
        }

        $parts = explode('.', $jwt);
        $payload = $parts[1] ?? '';
        if ($payload === '') {
            return [];
        }

        $payload = str_replace(['-', '_'], ['+', '/'], $payload);
        $padding = strlen($payload) % 4;
        if ($padding > 0) {
            $payload .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($payload, true);
        if ($decoded === false) {
            return [];
        }

        $json = json_decode($decoded, true);
        return is_array($json) ? $json : [];
    }

    /**
     * @param array<string, string|null> $data
     */
    private function findOrCreateSocialUser(string $provider, array $data): User
    {
        $providerId = (string) ($data['provider_id'] ?? '');
        $email = strtolower(trim((string) ($data['email'] ?? '')));

        $user = $this->socialAccountService->findUserByProvider($provider, $providerId);

        if (!$user && $email !== '') {
            $user = User::where('email', $email)->first();
        }

        if ($user) {
            $needsSave = false;
            if ((string) $user->provider !== $provider) {
                $user->provider = $provider;
                $needsSave = true;
            }
            if ((string) $user->provider_id !== $providerId) {
                $user->provider_id = $providerId;
                $needsSave = true;
            }
            if ($needsSave) {
                $user->save();
            }
            return $user;
        }

        $name = trim((string) ($data['name'] ?? ''));
        $firstName = trim((string) ($data['first_name'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? ''));

        if ($firstName === '' && $name !== '') {
            $parts = preg_split('/\s+/', $name) ?: [];
            $firstName = trim((string) ($parts[0] ?? ''));
            if ($lastName === '' && count($parts) > 1) {
                $lastName = trim(implode(' ', array_slice($parts, 1)));
            }
        }

        $safeEmail = $email;
        if ($safeEmail === '') {
            $safeEmail = "{$provider}_{$providerId}@social.local";
        }

        $user = new User();
        $user->username = $this->generateUniqueUsername($safeEmail);
        $user->firstname = $firstName !== '' ? Str::limit($firstName, 40, '') : null;
        $user->lastname = $lastName !== '' ? Str::limit($lastName, 40, '') : null;
        $user->email = Str::limit($safeEmail, 40, '');
        $user->password = Hash::make(Str::random(40));
        $user->provider = $provider;
        $user->provider_id = $providerId;
        $user->status = 1;
        $user->ev = 1;
        $user->sv = 1;
        $user->ts = 0;
        $user->tv = 1;
        $user->profile_complete = 0;
        $user->ref_by = 0;
        $user->referral_code = method_exists(User::class, 'getUniqueReferralCode')
            ? User::getUniqueReferralCode()
            : null;
        $user->save();

        return $user;
    }

    private function generateUniqueUsername(string $seed): string
    {
        $seed = strtolower($seed);
        $seed = str_contains($seed, '@') ? strstr($seed, '@', true) : $seed;
        $base = preg_replace('/[^a-z0-9_]+/i', '_', $seed ?? '') ?: 'usuario';
        $base = trim($base, '_');
        if ($base === '') {
            $base = 'usuario';
        }

        $base = Str::limit($base, 30, '');
        $candidate = $base;
        $counter = 0;

        while (User::where('username', $candidate)->exists()) {
            $counter++;
            $suffix = '_' . $counter;
            $candidate = Str::limit($base, max(3, 40 - strlen($suffix)), '') . $suffix;
        }

        return $candidate;
    }

    private function logUserLogin(User $user, Request $request): void
    {
        $ip = $request->ip();
        $exist = UserLogin::where('user_ip', $ip)->first();

        $userLogin = new UserLogin();
        if ($exist) {
            $userLogin->longitude = $exist->longitude;
            $userLogin->latitude = $exist->latitude;
            $userLogin->city = $exist->city;
            $userLogin->country = $exist->country;
            $userLogin->country_code = $exist->country_code;
        } else {
            $info = getIpInfo();
            $userLogin->longitude = $info['lon'] ?? $info['long'] ?? null;
            $userLogin->latitude = $info['lat'] ?? null;
            $userLogin->city = $info['city'] ?? null;
            $userLogin->country = $info['country'] ?? null;
            $userLogin->country_code = $info['countryCode'] ?? $info['code'] ?? null;
        }

        $osBrowser = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;
        $userLogin->browser = $osBrowser['browser'] ?? null;
        $userLogin->os = $osBrowser['os_platform'] ?? null;
        $userLogin->save();
    }

    private function failRedirect(string $message): RedirectResponse
    {
        return redirect()->to($this->consumeReturnUrl())->withErrors(['social' => $message]);
    }
}
