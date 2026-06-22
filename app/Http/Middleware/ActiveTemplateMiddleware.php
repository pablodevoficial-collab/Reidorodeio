<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class ActiveTemplateMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $template = Session::get('template', gs('active_template') ?? 'basic');
        Session::put('template', $template);

        View::share('activeTemplate', activeTemplate());
        View::share('activeTemplateTrue', activeTemplate(true));

        return $next($request);
    }
}
