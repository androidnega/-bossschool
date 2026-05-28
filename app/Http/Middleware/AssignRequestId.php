<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tag every request with a stable id available to:
 *  - the exception handler (so 500 pages can render it)
 *  - the structured logger (added as a "request_id" context value)
 *  - upstream proxies / clients (returned as the X-Request-Id response header)
 *
 * The middleware respects an incoming X-Request-Id header (so a CDN/LB can
 * stitch traces) and falls back to a fresh 32-char hex id.
 */
class AssignRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $incoming = (string) $request->headers->get('X-Request-Id', '');
        $id = preg_match('/^[A-Za-z0-9\-]{8,64}$/', $incoming) === 1
            ? $incoming
            : bin2hex(random_bytes(16));

        $request->headers->set('X-Request-Id', $id);
        app()->instance('request.id', $id);

        Log::shareContext([
            'request_id' => $id,
            'tenant_id' => optional($request->user())->tenant_id,
            'user_id' => optional($request->user())->id,
        ]);

        $response = $next($request);
        $response->headers->set('X-Request-Id', $id);

        return $response;
    }
}
