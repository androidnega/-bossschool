<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permissions\SyncUserPermissionsRequest;
use App\Models\Permission;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserPermissionsController extends Controller
{
    public function __construct(
        private readonly PermissionService $permissions,
        private readonly ActivityLogger $logger,
    ) {}

    public function index(): View
    {
        $this->authorizeAdmin();

        $users = User::query()->where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')->paginate(25);

        $catalogue = Permission::query()->orderBy('module')->orderBy('key')->get()->groupBy('module');

        $assignableRoles = [
            UserRole::Admin->value,
            UserRole::Accountant->value,
            UserRole::Teacher->value,
            UserRole::Headteacher->value,
            UserRole::ExamOfficer->value,
            UserRole::AttendanceOfficer->value,
            UserRole::Librarian->value,
            UserRole::InventoryOfficer->value,
            UserRole::DisciplineOfficer->value,
        ];

        return view('user_permissions.index', [
            'users' => $users,
            'catalogue' => $catalogue,
            'assignableRoles' => $assignableRoles,
            'allKeys' => array_keys($catalogue->collapse()->keyBy('key')->all()),
        ]);
    }

    public function edit(User $user): View
    {
        $this->authorizeAdmin();
        $this->ensureSameTenant($user);

        $catalogue = Permission::query()->orderBy('module')->orderBy('key')->get()->groupBy('module');
        $userKeys = $user->permissions()->pluck('key')->all();

        $assignableRoles = [
            UserRole::Admin->value,
            UserRole::Accountant->value,
            UserRole::Teacher->value,
            UserRole::Headteacher->value,
            UserRole::ExamOfficer->value,
            UserRole::AttendanceOfficer->value,
            UserRole::Librarian->value,
            UserRole::InventoryOfficer->value,
            UserRole::DisciplineOfficer->value,
        ];

        return view('user_permissions.edit', compact('user', 'catalogue', 'userKeys', 'assignableRoles'));
    }

    public function update(SyncUserPermissionsRequest $request, User $user): RedirectResponse
    {
        $this->ensureSameTenant($user);

        $actor = $request->user();

        // Tenant Admins cannot promote a user to SuperAdmin.
        if ($request->filled('role')) {
            $role = (string) $request->input('role');
            if ($role === UserRole::SuperAdmin->value) {
                abort(403);
            }
            if (in_array($role, array_map(fn ($c) => $c->value, UserRole::cases()), true)) {
                $user->role = $role;
                $user->save();
            }
        }

        $keys = (array) $request->input('permissions', []);
        $this->permissions->syncUserPermissions($user, $keys, $actor);

        $this->logger->log(
            'user_permissions_synced',
            sprintf('Permissions updated for %s', $user->email),
            ['user_id' => $user->id, 'permissions' => $keys, 'role' => $user->role],
            $user->tenant_id ? (int) $user->tenant_id : null,
            User::class,
            (int) $user->id
        );

        return redirect()->route('user-permissions.index')->with('status', __('Permissions saved.'));
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
        $current = auth()->user();
        if ((int) $current->tenant_id !== (int) $user->tenant_id) {
            abort(403);
        }
    }
}
