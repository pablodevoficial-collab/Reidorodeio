<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
class RedirectIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'admin')
    {
        if (Auth::guard($guard)->check()) {
            if (\Illuminate\Support\Facades\Route::has('admin.dashboard')) {
                return to_route('admin.dashboard');
            }
            // Fallback por URL para evitar RouteNotFoundException em ambientes com cache/rotas parciais
            return redirect('/admin/dashboard');
        }
        return $next($request);
    }
}
