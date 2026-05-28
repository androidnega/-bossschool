<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defence-in-depth response headers for the web UI.
 *
 * - Content-Security-Policy: allow the CDN-based asset pipeline used in
 *   layouts/partials/head-assets.blade.php:
 *     • Tailwind Play CDN  (cdn.tailwindcss.com) — script + injected <style>
 *       + JIT compiler which needs 'unsafe-eval'.
 *     • Google Fonts        (fonts.googleapis.com + fonts.gstatic.com)
 *     • Font Awesome        (cdnjs.cloudflare.com)
 * - X-Frame-Options + frame-ancestors: prevents clickjacking.
 * - X-Content-Type-Options: blocks MIME-sniffing on uploads.
 * - Referrer-Policy: hides full URLs from outbound links.
 * - Strict-Transport-Security: only emitted on HTTPS requests so local dev
 *   over plain HTTP still works.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $cdnjs    = 'https://cdnjs.cloudflare.com';
        $tailwind = 'https://cdn.tailwindcss.com';
        $gFontsCss   = 'https://fonts.googleapis.com';
        $gFontsFiles = 'https://fonts.gstatic.com';

        $csp = implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "img-src 'self' data: blob:",
            "font-src 'self' data: {$cdnjs} {$gFontsFiles}",
            "style-src 'self' 'unsafe-inline' {$cdnjs} {$tailwind} {$gFontsCss}",
            "style-src-elem 'self' 'unsafe-inline' {$cdnjs} {$tailwind} {$gFontsCss}",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' {$tailwind}",
            "script-src-elem 'self' 'unsafe-inline' {$tailwind}",
            "connect-src 'self' {$tailwind}",
            "form-action 'self'",
        ]);

        if (! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
