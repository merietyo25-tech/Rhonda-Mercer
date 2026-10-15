<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Referrer-Policy',          'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options',   'nosniff');
        $response->headers->set('X-Frame-Options',          'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection',         '1; mode=block');
        $response->headers->set('Permissions-Policy',       'camera=(), microphone=(), geolocation=()');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Politique volontairement permissive sur 'unsafe-inline' : l'app utilise beaucoup
        // de <script>/<style> inline dans les vues Blade — une CSP stricte casserait ces
        // pages. Ceci bloque déjà l'injection de ressources depuis des origines externes.
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https: http:",
            "style-src 'self' 'unsafe-inline' https: http:",
            "img-src 'self' data: https: http:",
            "font-src 'self' data: https: http:",
            "connect-src 'self' https: http:",
            "frame-src 'self' https://www.openstreetmap.org",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "object-src 'none'",
        ]));

        return $response;
    }
}
