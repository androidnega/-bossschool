<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\AuthService;
use App\Services\TwoFactorService;
use App\Support\TenantResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function show(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $email = (string) $request->validated('email');
        $password = (string) $request->validated('password');

        $throttleKey = $this->auth->throttleKey($request, $email);

        if ($this->auth->hitsTooManyAttempts($throttleKey)) {
            $this->throwLockout($throttleKey);
        }

        $tenant = TenantResolver::resolveFromRequest($request);
        $tenantId = $tenant?->id;

        $user = User::findForCredentials($email, $password, $tenantId);

        if ($user === null) {
            $seconds = $this->auth->recordFailure($throttleKey);

            $activityLogger->log(
                'login_failed',
                'Failed login attempt',
                ['email' => $email, 'tenant_id' => $tenantId, 'host' => $request->getHost()],
                $tenantId
            );

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (! (bool) ($user->is_active ?? true)) {
            $activityLogger->log(
                'login_blocked_deactivated',
                'Login blocked: user is deactivated',
                ['email' => $email, 'tenant_id' => $tenantId],
                $tenantId
            );

            throw ValidationException::withMessages([
                'email' => __('This account is deactivated. Contact your administrator.'),
            ]);
        }

        $this->auth->clearAttempts($throttleKey);

        // 2FA gate: if the user has 2FA enabled, stash their id and bounce
        // them through the challenge screen before we hand out a session.
        $twoFactor = app(TwoFactorService::class);
        if ($twoFactor->isEnabled($user)) {
            $request->session()->put('2fa.user_id', $user->id);
            $request->session()->put('2fa.remember', $request->boolean('remember'));

            $activityLogger->log(
                '2fa_challenge_required',
                'Login held pending 2FA challenge',
                ['email' => $user->email, 'tenant_id' => $user->tenant_id],
                $user->tenant_id,
                User::class,
                $user->id
            );

            return redirect()->route('two-factor.challenge.show');
        }

        $user->forceFill(['last_login_at' => now()])->save();

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $activityLogger->log(
            'login',
            'User logged in',
            ['email' => $user->email, 'tenant_id' => $user->tenant_id, 'host' => $request->getHost()],
            $user->tenant_id,
            User::class,
            $user->id
        );

        return redirect()->intended($user->homeRoute());
    }

    public function logout(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        if (Auth::check()) {
            $activityLogger->log('logout', 'User logged out');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function throwLockout(string $throttleKey): void
    {
        $seconds = $this->auth->secondsUntilAvailable($throttleKey);

        throw ValidationException::withMessages([
            'email' => __('Too many login attempts. Try again in :seconds seconds.', ['seconds' => $seconds]),
        ])->status(429);
    }
}
