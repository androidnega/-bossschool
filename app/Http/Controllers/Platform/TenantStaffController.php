<?php

namespace App\Http\Controllers\Platform;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\View\View;

class TenantStaffController extends Controller
{
    public function index(Tenant $tenant): View
    {
        $this->authorize('platform.view');

        $staff = Staff::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        $teachers = User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('role', UserRole::Teacher->value)
            ->get(['id', 'name', 'email']);

        $linked = $staff->map(function (Staff $row) use ($teachers) {
            $match = $teachers->first(fn (User $u): bool => strcasecmp(trim($u->name), trim($row->name)) === 0);

            return [$row, $match];
        });

        return view('platform.tenant-staff.index', [
            'tenant' => $tenant,
            'linked' => $linked,
        ]);
    }
}
