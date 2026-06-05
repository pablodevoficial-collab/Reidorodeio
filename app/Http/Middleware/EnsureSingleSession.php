<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class EnsureSingleSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip for admin routes to prevent session invalidation during admin login (session regeneration)
        if ($request->is('admin') || $request->is('admin/*')) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            
            // Força a busca do dado mais recente no banco de dados
            // Isso previne que usemos uma instância de User em cache que ainda tem o ID antigo
            $currentSessionId = \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $user->id)
                ->value('current_session_id');
            
            // Verifica se existe uma sessão salva e se é diferente da atual
            if ($currentSessionId && $currentSessionId !== Session::getId()) {
                
                // Realiza logout
                Auth::guard('web')->logout();
                
                // Invalida a sessão
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Redireciona com mensagem de erro
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['message' => 'Sessão encerrada. Conta acessada em outro local.'], 401);
                }

                return redirect()->route('home')->withErrors([
                    'username' => 'Sessão encerrada. Sua conta foi acessada em outro dispositivo.'
                ]);
            }
        }

        return $next($request);
    }
}
