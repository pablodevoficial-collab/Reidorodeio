<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            // Se o guard for admin, manda pro dashboard.
            if ($guard === 'admin') {
                return to_route('admin.dashboard');
            }

            // Se nenhum guard foi especificado, mas o admin está logado E a request é pra área admin...
            if (!$guard && Auth::guard('admin')->check() && ($request->is('admin') || $request->is('admin/*'))) {
                return to_route('admin.dashboard');
            }
            
            // Caso contrário, entrada principal atual do usuário
            return to_route('home');
        }

        return $next($request);
    }
}
