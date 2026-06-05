<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileWebviewController extends Controller
{
    public function entry(Request $request): RedirectResponse
    {
        $userId = (int) $request->query('uid', 0);
        $user = User::find($userId);

        if (!$user || (int) $user->status === 0) {
            return redirect()->route('home')->withErrors(['social' => 'Sessão do app inválida. Faça login novamente.']);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->regenerate();

        Auth::guard('web')->login($user, $request->boolean('remember', true));
        $request->session()->regenerate();

        $user->current_session_id = $request->session()->getId();
        $user->save();
        $request->session()->save();

        return redirect()->to($this->resolveHubUrl($request));
    }

    private function resolveHubUrl(Request $request): string
    {
        $base = rtrim($request->getSchemeAndHttpHost(), '/');
        $tab = trim((string) $request->query('tab', 'inicio'));

        $path = match ($tab) {
            'equipes' => '/equipes',
            'x1' => '/x1',
            'estatisticas' => '/estatisticas',
            'perfil' => '/perfil',
            'individuais' => '/individuais',
            'premium' => '/premium-tab',
            default => '/',
        };

        $params = array_filter([
            'app' => $request->filled('app') ? (string) $request->query('app') : null,
            'platform' => $request->filled('platform') ? (string) $request->query('platform') : null,
            'tab' => $tab !== '' ? $tab : null,
        ], static fn ($value) => $value !== null && $value !== '');

        $query = $params === [] ? '' : ('?' . http_build_query($params));

        return $base . $path . $query;
    }
}
