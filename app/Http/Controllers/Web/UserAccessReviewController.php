<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserAccessReviewController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $this->authorizeAdmin();
        $tenantId = (int) auth()->user()->tenant_id;

        $cutoff = now()->subDays((int) $request->input('inactive_days', 60));

        $users = User::query()
            ->where('tenant_id', $tenantId)
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        $inactive = User::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($cutoff): void {
                $q->whereNull('last_login_at')->orWhere('last_login_at', '<', $cutoff);
            })
            ->orderBy('last_login_at')
            ->limit(100)
            ->get();

        return view('user_access_review.index', [
            'users' => $users,
            'inactive' => $inactive,
            'roles' => array_map(fn ($r) => $r->value, UserRole::cases()),
            'filters' => ['role' => $request->input('role'), 'inactive_days' => (int) $request->input('inactive_days', 60)],
        ]);
    }

    public function forceReset(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();
        $this->ensureSameTenant($user);

        $user->forceFill(['force_password_reset' => true])->save();

        $this->logger->log(
            'force_password_reset_requested',
            'Force password reset enabled for user',
            ['user_id' => $user->id],
            (int) $user->tenant_id,
            User::class,
            (int) $user->id
        );

        return back()->with('status', __('User will be required to reset their password.'));
    }

    public function revokeSessions(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();
        $this->ensureSameTenant($user);

        // Sanctum tokens
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }
        // DB sessions (if "session" driver = database). Best-effort.
        try {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        } catch (\Throwable $e) {
            // ignore: sessions table may not be DB-driver in tests
        }

        $user->forceFill(['remember_token' => null])->save();

        $this->logger->log(
            'sessions_revoked',
            'All sessions/tokens revoked for user',
            ['user_id' => $user->id],
            (int) $user->tenant_id,
            User::class,
            (int) $user->id
        );

        return back()->with('status', __('All sessions revoked.'));
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();
        $this->ensureSameTenant($user);

        if (auth()->id() === $user->id) {
            abort(403);
        }

        $user->forceFill(['is_active' => false])->save();
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }
        try {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        } catch (\Throwable $e) {
            // ignore
        }

        $this->logger->log(
            'user_deactivated',
            'User deactivated (history preserved)',
            ['user_id' => $user->id],
            (int) $user->tenant_id,
            User::class,
            (int) $user->id
        );

        return back()->with('status', __('User deactivated.'));
    }

    private function authorizeAdmin(): void
    {
        $u = auth()->user();
        if (! $u || ! in_array($u->role, [UserRole::Admin->value, UserRole::Proprietor->value], true)) {
            abort(403);
        }
    }

    private function ensureSameTenant(User $user): void
    {
        if ((int) $user->tenant_id !== (int) auth()->user()->tenant_id) {
            abort(403);
        }
    }
}
