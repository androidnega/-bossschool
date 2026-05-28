<?php

namespace App\Http\Middleware;

use App\Services\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * If the current user belongs to a 2FA-required role and has not yet
 * enrolled, redirect every request (except the 2FA setup flow itself, the
 * logout endpoint, and assets) to /two-factor.
 *
 * Configured globally via the 'web' middleware group so privileged users
 * physically cannot reach any school screen without enrolling.
 */
class EnforceTwoFactor
{
    public function __construct(private readonly TwoFactorService $service) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        // Hard kill switch: production defaults to ON; the test suite leaves
        // it OFF unless a specific test opts in. We never disable in prod
        // accidentally because `app.enforce_2fa` is read at request time.
        if (! (bool) config('auth.enforce_2fa', true)) {
            return $next($request);
        }

        if (! $this->service->requiresEnrolment($user)) {
            return $next($request);
        }

        // Allow the user to: visit the public marketing homepage, the setup
        // pages, log out, or hit the public health endpoint. Logged-in users
        // retain access to public-facing routes even before they finish 2FA
        // enrolment; only the protected app screens are gated.
        $allowed = [
            'home',
            'two-factor.show',
            'two-factor.enable',
            'two-factor.enable.show',
            'two-factor.confirm',
            'two-factor.disable',
            'logout',
            'health',
        ];

        $routeName = optional($request->route())->getName();
        if ($routeName !== null && in_array($routeName, $allowed, true)) {
            return $next($request);
        }

        // Don't intercept JSON / API calls (sanctum) with a redirect.
        if ($request->expectsJson()) {
            return response()->json(['error' => 'two_factor_enrolment_required'], 403);
        }

        return redirect()->route('two-factor.show');
    }
}
