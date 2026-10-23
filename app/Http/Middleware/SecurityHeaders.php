<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds the response headers a browser needs to defend the app on its own.
 *
 * Runs globally rather than in a middleware group so Livewire's message
 * requests, downloads and error pages are covered too.
 *
 * There is deliberately no script-src/style-src policy: the layout and the
 * chart blocks rely on inline <script> and inline style attributes throughout,
 * so any useful policy would have to allow 'unsafe-inline' and would buy
 * nothing. frame-ancestors is unaffected by that and is the directive worth
 * having. CSP only enforces the directives actually present, so naming just
 * this one leaves everything else unrestricted rather than blocked.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // Clickjacking. frame-ancestors supersedes X-Frame-Options, which is
            // kept for browsers that do not read CSP.
            'Content-Security-Policy'           => "frame-ancestors 'none'",
            'X-Frame-Options'                   => 'DENY',

            // Stop the browser guessing a type other than the one we sent.
            'X-Content-Type-Options'            => 'nosniff',

            // Send the full URL within the site, only the origin when leaving it.
            'Referrer-Policy'                   => 'strict-origin-when-cross-origin',

            // The app uses none of these; refusing them limits what injected
            // script could reach for.
            'Permissions-Policy'                => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',

            // Blocks the legacy Flash/PDF cross-domain policy files.
            'X-Permitted-Cross-Domain-Policies' => 'none',
        ];

        // Only meaningful over TLS, and sending it on plain HTTP local dev would
        // pin the developer's browser to https://menro.test.
        if ($request->secure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            // Never clobber a header a response set deliberately.
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
