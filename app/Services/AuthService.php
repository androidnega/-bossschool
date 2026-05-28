<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public const MAX_ATTEMPTS = 5;

    public const DECAY_SECONDS = 60;

    /**
     * Attempt a tenant-scoped login. Pass the tenant id resolved from the
     * request host (or null when the user is signing in on the bare platform
     * host as SuperAdmin).
     *
     * @return array{user: User, token: string}|null
     */
    public function attemptLogin(string $email, string $password, ?int $tenantId = null): ?array
    {
        $user = User::findForCredentials($email, $password, $tenantId);

        if ($user === null) {
            return null;
        }

        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function revokeCurrentToken(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    /**
     * Stable rate-limit key per email + IP. Email is hashed and lowercased
     * so attackers cannot bypass the limit by varying case.
     */
    public function throttleKey(Request $request, string $email): string
    {
        $email = Str::lower(trim($email));

        return 'login|'.sha1($email).'|'.$request->ip();
    }

    public function hitsTooManyAttempts(string $key): bool
    {
        return RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS);
    }

    public function recordFailure(string $key): int
    {
        RateLimiter::hit($key, self::DECAY_SECONDS);

        return RateLimiter::availableIn($key);
    }

    public function clearAttempts(string $key): void
    {
        RateLimiter::clear($key);
    }

    public function secondsUntilAvailable(string $key): int
    {
        return RateLimiter::availableIn($key);
    }
}
