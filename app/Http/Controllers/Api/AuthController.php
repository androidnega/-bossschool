<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ActivityLogger;
use App\Services\AuthService;
use App\Support\TenantResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $auth, ActivityLogger $activityLogger): JsonResponse
    {
        $validated = $request->validated();
        $email = (string) $validated['email'];
        $password = (string) $validated['password'];

        $throttleKey = $auth->throttleKey($request, $email);

        if ($auth->hitsTooManyAttempts($throttleKey)) {
            $seconds = $auth->secondsUntilAvailable($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('Too many login attempts. Try again in :seconds seconds.', ['seconds' => $seconds]),
            ])->status(429);
        }

        $tenant = TenantResolver::resolveFromRequest($request);
        $tenantId = $tenant?->id;

        $result = $auth->attemptLogin($email, $password, $tenantId);

        if ($result === null) {
            $auth->recordFailure($throttleKey);

            $activityLogger->log(
                'login_failed',
                'Failed API login attempt',
                ['email' => $email, 'tenant_id' => $tenantId, 'host' => $request->getHost()],
                $tenantId
            );

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $auth->clearAttempts($throttleKey);

        $activityLogger->log(
            'login',
            'User logged in (API)',
            ['email' => $result['user']->email, 'tenant_id' => $result['user']->tenant_id],
            $result['user']->tenant_id,
            \App\Models\User::class,
            $result['user']->id
        );

        return response()->json([
            'token' => $result['token'],
            'user' => $result['user'],
        ]);
    }

    public function logout(Request $request, AuthService $auth): JsonResponse
    {
        $auth->revokeCurrentToken($request->user());

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
