<?php

namespace App\Http\Middleware;

use App\Models\FeatureToggle;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    /**
     * @param  string  ...$features  First argument is the feature key from route parameter
     */
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $user = $request->user();

        if ($user?->isSuperAdmin()) {
            return $next($request);
        }

        foreach ($features as $key) {
            $key = trim($key);
            if ($key === '' || ! FeatureToggle::isGloballyEnabled($key)) {
                abort(503, __('This feature is temporarily unavailable.'));
            }
        }

        return $next($request);
    }
}
