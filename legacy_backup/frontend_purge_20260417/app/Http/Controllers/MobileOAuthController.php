<?php

namespace App\Http\Controllers;

use App\Services\UserSocialAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileOAuthController extends Controller
{
    public function __construct(
        private readonly UserSocialAccountService $socialAccountService
    ) {
    }

    public function socialStart(Request $request, string $provider): RedirectResponse
    {
        $provider = $this->normalizeProvider($provider);
        if (!$provider) {
            return redirect()->away($this->buildAppUri('oauth-error', [
                'message' => 'Provedor social invalido.',
            ]));
        }

        $platform = trim((string) $request->query('platform', 'android'));
        $redirectUrl = route('user.social.redirect', ['provider' => $provider]);
        $mode = trim((string) $request->query('mode', ''));
        $linkToken = trim((string) $request->query('link_token', ''));

        if (Auth::check() && $mode !== 'link') {
            $query = $mode === 'link' && $linkToken !== ''
                ? ['mode' => 'link', 'link_token' => $linkToken]
                : [];

            return redirect()->to(
                route('mobile.oauth.complete', ['provider' => $provider]) .
                (empty($query) ? '' : ('?' . http_build_query($query)))
            );
        }

        $returnUrl = route('mobile.oauth.complete', ['provider' => $provider]);
        $queryData = [
            'return_url' => $returnUrl,
            'app' => '1',
            'source' => 'app',
            'platform' => $platform,
        ];

        if ($mode === 'link' && $linkToken !== '') {
            $queryData['return_url'] = $returnUrl . '?' . http_build_query([
                'mode' => 'link',
                'link_token' => $linkToken,
            ]);
            $queryData['mode'] = 'link';
            $queryData['link_token'] = $linkToken;
        }

        $query = http_build_query($queryData);

        return redirect()->away($redirectUrl . '?' . $query);
    }

    public function socialComplete(Request $request, string $provider): RedirectResponse
    {
        $provider = $this->normalizeProvider($provider);
        if (!$provider) {
            return redirect()->away($this->buildAppUri('oauth-error', [
                'message' => 'Provedor social invalido.',
            ]));
        }

        $mode = trim((string) $request->query('mode', ''));
        $linkToken = trim((string) $request->query('link_token', ''));

        if ($mode === 'link' && $linkToken !== '') {
            if ($this->socialAccountService->isSuccessfulMobileLink($linkToken, $provider)) {
                return redirect()->away($this->buildAppUri('oauth-link-success', [
                    'provider' => $provider,
                ]));
            }

            return redirect()->away($this->buildAppUri('oauth-error', [
                'message' => 'Nao foi possivel concluir a conexao da conta social.',
            ]));
        }

        try {
            if (!Auth::check()) {
                return redirect()->away($this->buildAppUri('oauth-error', [
                    'message' => 'Falha no login social. Tente novamente.',
                ]));
            }

            /** @var \App\Models\User $user */
            $user = $request->user();
            $token = $user->createToken('mobile:' . $provider, ['mobile'])->plainTextToken;

            return redirect()->away($this->buildAppUri('oauth-success', [
                'token' => $token,
                'provider' => $provider,
            ]));
        } catch (\Throwable $e) {
            report($e);

            return redirect()->away($this->buildAppUri('oauth-error', [
                'message' => 'Nao foi possivel concluir o login social.',
            ]));
        }
    }

    private function normalizeProvider(string $provider): ?string
    {
        return $this->socialAccountService->normalizeProvider($provider);
    }

    /**
     * @param array<string, string> $params
     */
    private function buildAppUri(string $path, array $params = []): string
    {
        $query = empty($params) ? '' : ('?' . http_build_query($params));
        return "reiapp://{$path}{$query}";
    }
}
