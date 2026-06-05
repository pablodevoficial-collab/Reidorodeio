<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SkipNgrokWarning
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('ngrok-skip-browser-warning')) {
            return $next($request);
        }

        // Add header to skip ngrok warning page
        $request->headers->set('ngrok-skip-browser-warning', 'true');
        
        return $next($request);
    }
}
