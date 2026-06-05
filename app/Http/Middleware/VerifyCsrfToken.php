<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'user/password/email',
        'user/password/verify-code',
        'user/password/reset-code',
        'user/password/reset',
        'user/social/apple/callback',
        'api/mobile/*',
        'facebook/data-deletion',
        'admin/password/reset',
        'ipn*',
        'webhook*'
    ];
}
