<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPremium
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('user.login')
                ->with('error', 'Você precisa estar logado para acessar esta área.');
        }

        // Verificar se o usuário tem assinatura premium ativa
        $subscription = $user->subscriptions()
            ->where('status', 'ativa')
            ->where('data_fim', '>=', now())
            ->first();

        if (!$subscription) {
            return redirect()->route('premium.landing')
                ->with('error', 'Esta funcionalidade é exclusiva para usuários Premium. Faça sua assinatura para ter acesso.');
        }

        return $next($request);
    }
}
