<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\TenantResolver;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function showLinkRequest(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Always respond as if the email was sent, even when no matching user
     * exists - this prevents account enumeration. The Password broker
     * silently no-ops for unknown emails.
     */
    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $email = (string) $request->validated('email');

        $tenant = TenantResolver::resolveFromRequest($request);
        $tenantId = $tenant?->id;

        $user = $this->resolveUserForReset($email, $tenantId);

        if ($user !== null) {
            $status = Password::broker()->sendResetLink(['email' => $user->email]);

            $this->activityLogger->log(
                'password_reset_requested',
                'Password reset link requested',
                [
                    'email' => $email,
                    'tenant_id' => $user->tenant_id,
                    'broker_status' => (string) $status,
                ],
                $user->tenant_id,
                User::class,
                $user->id
            );
        } else {
            $this->activityLogger->log(
                'password_reset_requested',
                'Password reset attempted for unknown email',
                ['email' => $email, 'tenant_id' => $tenantId],
                $tenantId
            );
        }

        return back()->with('status', __('If an account exists for that email, a password reset link has been sent.'));
    }

    public function showReset(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(ResetPasswordRequest $request): RedirectResponse
    {
        $tenant = TenantResolver::resolveFromRequest($request);
        $tenantId = $tenant?->id;

        $email = (string) $request->validated('email');
        $user = $this->resolveUserForReset($email, $tenantId);

        if ($user === null) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('passwords.user')]);
        }

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $broker, string $plain) use ($request, $user): void {
                $broker->forceFill([
                    'password' => Hash::make($plain),
                    'remember_token' => Str::random(60),
                ])->save();

                $broker->tokens()->delete();

                event(new PasswordReset($broker));

                $this->activityLogger->log(
                    'password_reset_completed',
                    'User completed password reset',
                    [
                        'email' => $broker->email,
                        'tenant_id' => $broker->tenant_id,
                        'host' => $request->getHost(),
                    ],
                    $broker->tenant_id,
                    User::class,
                    $broker->id
                );
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __('Your password has been reset. Please sign in with your new password.'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }

    /**
     * Pick the right candidate user. When a tenant host is in play, scope to
     * that tenant + SuperAdmin. On a bare host, only SuperAdmin in production;
     * in local/testing we allow a single global match to keep dev flow.
     */
    private function resolveUserForReset(string $email, ?int $tenantId): ?User
    {
        $query = User::query()->withoutGlobalScopes()->where('email', $email);

        $isProduction = ! in_array(app()->environment(), ['local', 'testing'], true);

        if ($tenantId === null && $isProduction) {
            $query->whereNull('tenant_id');
        } elseif ($tenantId !== null) {
            $query->where(function ($q) use ($tenantId): void {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            });
        }

        $candidates = $query->get();

        if ($candidates->count() <= 1) {
            return $candidates->first();
        }

        if ($tenantId !== null) {
            return $candidates->first(fn (User $u) => (int) $u->tenant_id === (int) $tenantId) ?? $candidates->first();
        }

        return null;
    }
}
