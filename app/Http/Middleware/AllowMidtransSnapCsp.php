<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowMidtransSnapCsp
{
    /**
     * Handle an incoming request.
     * Set CSP to allow Midtrans Snap (requires unsafe-eval) on admin panel.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Midtrans Snap uses eval() - require unsafe-eval. Applied only to admin panel.
        // In local env allow http: for img-src so http://localhost:8000/storage/* images work (CSP blocks them otherwise).
        $imgSrc = config('app.env') === 'local'
            ? "'self' data: https: http: blob:"
            : "'self' data: https: blob:";

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://app.sandbox.midtrans.com https://app.midtrans.com https://*.midtrans.com blob:",
            "frame-src 'self' https://app.sandbox.midtrans.com https://app.midtrans.com https://*.midtrans.com blob:",
            "connect-src 'self' https://app.sandbox.midtrans.com https://app.midtrans.com https://*.midtrans.com wss: ws: https: blob:",
            "style-src 'self' 'unsafe-inline' https:",
            "img-src {$imgSrc}",
            "font-src 'self' data: https:",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
