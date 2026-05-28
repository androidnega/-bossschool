<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Mid-login challenge:
 *
 *   1. AuthController authenticates email+password but, if 2FA is enabled,
 *      logs the user out again, stashes their id in the session, and
 *      redirects to GET /two-factor/challenge.
 *   2. The challenge view accepts either a 6-digit TOTP or a recovery code.
 *   3. On success we Auth::loginUsingId and set session.two_factor_passed.
 *   4. On failure we record an activity log row for triage; the rate
 *      limiter inherited from AuthService still applies.
 *
 * This split keeps the existing AuthController very small and avoids a
 * subtle bug where a partially authenticated user could navigate to
 * privileged screens.
 */
class TwoFactorChallengeController extends Controller
{
    public function __construct(
        private readonly TwoFactorService $service,
        private readonly ActivityLogger $logger,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $userId = (int) $request->session()->get('2fa.user_id', 0);
        if (! $userId) {
            return redirect()->route('login');
        }

        return view('two_factor.challenge');
    }

    public function attempt(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        if (empty($data['code']) && empty($data['recovery_code'])) {
            throw ValidationException::withMessages(['code' => __('Enter a code.')]);
        }

        $userId = (int) $request->session()->get('2fa.user_id', 0);
        $remember = (bool) $request->session()->get('2fa.remember', false);

        $user = $userId ? User::withoutGlobalScopes()->find($userId) : null;
        if ($user === null) {
            return redirect()->route('login');
        }

        $ok = false;
        if (! empty($data['code'])) {
            $ok = $this->service->verifyTotp($user, (string) $data['code']);
        }
        if (! $ok && ! empty($data['recovery_code'])) {
            $ok = $this->service->consumeRecoveryCode($user, (string) $data['recovery_code']);
        }

        if (! $ok) {
            $this->logger->log(
                '2fa_challenge_failed',
                '2FA challenge failed',
                ['user_id' => $user->id],
                $user->tenant_id ? (int) $user->tenant_id : null,
                User::class,
                (int) $user->id
            );

            throw ValidationException::withMessages(['code' => __('Invalid code.')]);
        }

        $request->session()->forget(['2fa.user_id', '2fa.remember']);
        Auth::loginUsingId($user->id, $remember);
        $request->session()->put('two_factor_passed', true);
        $request->session()->regenerate();

        $this->logger->log(
            '2fa_challenge_passed',
            '2FA challenge passed',
            ['user_id' => $user->id],
            $user->tenant_id ? (int) $user->tenant_id : null,
            User::class,
            (int) $user->id
        );

        return redirect()->intended($user->homeRoute());
    }
}
