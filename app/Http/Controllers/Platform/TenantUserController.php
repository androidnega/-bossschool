<?php

namespace App\Http\Controllers\Platform;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreTenantUserRequest;
use App\Http\Requests\Platform\UpdateTenantUserRequest;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantUserController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function index(Tenant $tenant): View
    {
        $this->authorize('platform.manageTenantUsers');

        $users = User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(30);

        return view('platform.tenant-users.index', compact('tenant', 'users'));
    }

    public function create(Tenant $tenant): View
    {
        $this->authorize('platform.manageTenantUsers');

        $roles = [
            UserRole::Proprietor->value,
            UserRole::Admin->value,
            UserRole::Accountant->value,
            UserRole::Teacher->value,
            UserRole::Parent->value,
            UserRole::Student->value,
        ];

        $students = Student::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get(['id', 'name', 'class_id']);

        return view('platform.tenant-users.create', compact('tenant', 'roles', 'students'));
    }

    public function store(StoreTenantUserRequest $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validated();

        $user = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'student_id' => $data['role'] === UserRole::Student->value ? ($data['student_id'] ?? null) : null,
            'email_verified_at' => now(),
        ]);

        $this->activityLogger->log(
            'user_created',
            'Tenant user created: '.$user->email,
            ['role' => $user->role],
            $tenant->id,
            User::class,
            $user->id
        );

        return redirect()
            ->route('platform.tenants.users.edit', [$tenant, $user])
            ->with('status', __('User created.'));
    }

    public function edit(Tenant $tenant, User $user): View
    {
        $this->authorize('platform.manageTenantUsers');
        $this->assertTenantUser($tenant, $user);

        $roles = [
            UserRole::Proprietor->value,
            UserRole::Admin->value,
            UserRole::Accountant->value,
            UserRole::Teacher->value,
            UserRole::Parent->value,
            UserRole::Student->value,
        ];

        $students = Student::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get(['id', 'name', 'class_id']);

        return view('platform.tenant-users.edit', compact('tenant', 'user', 'roles', 'students'));
    }

    public function update(UpdateTenantUserRequest $request, Tenant $tenant, User $user): RedirectResponse
    {
        $this->authorize('platform.manageTenantUsers');
        $this->assertTenantUser($tenant, $user);

        $data = $request->validated();
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'student_id' => $data['role'] === UserRole::Student->value ? ($data['student_id'] ?? null) : null,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $user->fill($payload);
        $user->save();

        $this->activityLogger->log(
            'user_updated',
            'Tenant user updated: '.$user->email,
            [],
            $tenant->id,
            User::class,
            $user->id
        );

        return redirect()
            ->route('platform.tenants.users.index', $tenant)
            ->with('status', __('User updated.'));
    }

    public function destroy(Request $request, Tenant $tenant, User $user): RedirectResponse
    {
        $this->authorize('platform.manageTenantUsers');
        $this->assertTenantUser($tenant, $user);

        if ($user->id === (int) $request->user()?->id) {
            abort(403);
        }

        $email = $user->email;
        $uid = $user->id;
        $user->delete();

        $this->activityLogger->log(
            'user_deleted',
            'Tenant user deleted: '.$email,
            ['user_id' => $uid],
            $tenant->id,
            User::class,
            $uid
        );

        return redirect()
            ->route('platform.tenants.users.index', $tenant)
            ->with('status', __('User removed.'));
    }

    private function assertTenantUser(Tenant $tenant, User $user): void
    {
        if ((int) $user->tenant_id !== (int) $tenant->id) {
            abort(404);
        }

        if ($user->role === UserRole::SuperAdmin->value) {
            abort(404);
        }
    }
}
