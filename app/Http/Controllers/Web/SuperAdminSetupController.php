<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SuperAdminSetupRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * One-shot bootstrap controller for creating the very first SuperAdmin.
 *
 * Accessible at /setup/superadmin. Self-locks: once any SuperAdmin exists
 * (a user with role=SuperAdmin and tenant_id IS NULL), the routes redirect
 * to /login. Useful for fresh installs where the operator wants a real
 * SuperAdmin account without running the seeder (which uses 'password').
 */
class SuperAdminSetupController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function show(): View|RedirectResponse
    {
        if ($this->superAdminExists()) {
            return redirect()->route('login')->with('status', __('A SuperAdmin already exists. Please sign in.'));
        }

        return view('auth.superadmin-setup');
    }

    public function store(SuperAdminSetupRequest $request): RedirectResponse
    {
        // Belt-and-braces: also check inside store(). Two requests racing
        // each other can both pass authorize() simultaneously.
        if ($this->superAdminExists()) {
            return redirect()->route('login')->with('status', __('A SuperAdmin already exists. Please sign in.'));
        }

        $data = $request->validated();

        $user = User::query()->create([
            'tenant_id' => null,
            'name' => $data['name'],
            'email' => $data['email'],
            // 'hashed' cast on the password column hashes on assign.
            'password' => $data['password'],
            'role' => UserRole::SuperAdmin->value,
            'is_active' => true,
            'force_password_reset' => false,
            'email_verified_at' => now(),
        ]);

        $this->logger->log(
            'superadmin_bootstrapped',
            'Initial SuperAdmin account created via /setup/superadmin',
            ['email' => $user->email],
            null,
            User::class,
            $user->id
        );

        return redirect()->route('login')->with('status', __('SuperAdmin account created. You can now sign in.'));
    }

    private function superAdminExists(): bool
    {
        return User::query()
            ->withoutGlobalScopes()
            ->whereNull('tenant_id')
            ->where('role', UserRole::SuperAdmin->value)
            ->exists();
    }
}
