<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class PlatformUserController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('platform.manage');

        $users = User::query()
            ->with('tenant')
            ->whereNotNull('tenant_id')
            ->orderByDesc('id')
            ->paginate(40);

        return view('platform.users', compact('users'));
    }
}
