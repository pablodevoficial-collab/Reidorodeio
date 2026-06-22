<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('admin');

        if (!$admin || !method_exists($admin, 'canAccessAdminRoute')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($admin->canAccessAdminRoute($routeName)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para acessar esta área.',
            ], 403);
        }

        $notify[] = ['error', 'Você não tem permissão para acessar esta área.'];

        return to_route('admin.dashboard')->with('notify', $notify);
    }
}
