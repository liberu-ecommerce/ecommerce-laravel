<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Content-Security-Policy directives.
     *
     * 'unsafe-eval' + 'unsafe-inline' are required by Livewire/Alpine (which eval
     * expressions from x-* attributes) and Filament (which injects inline styles).
     * img-src allows https:/data: because product images come from arbitrary
     * merchant URLs (seed data uses https://placehold.co).
     */
    private const CSP = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-eval' 'unsafe-inline'",
        "style-src 'self' 'unsafe-inline'",
        "img-src 'self' data: https:",
        "font-src 'self' data:",
        "connect-src 'self' ws: wss:",
        "frame-ancestors 'self'",
        "base-uri 'self'",
        "form-action 'self'",
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ponytail: Report-Only until the violation reports come back clean. Filament,
        // Livewire and Vite each pull in scripts/styles this policy has not been proven
        // against, and a wrong CSP breaks checkout silently. Flip to the enforcing
        // 'Content-Security-Policy' header once reports show no legitimate violations.
        $response->headers->set('Content-Security-Policy-Report-Only', implode('; ', self::CSP));

        // Never send HSTS over plain HTTP: a browser would pin the host to https and
        // lock out local dev, which serves http://localhost:8000.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
