<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Tenant;
use Illuminate\View\View;

class TenantMessageController extends Controller
{
    public function index(Tenant $tenant): View
    {
        $this->authorize('platform.view');

        $messages = Message::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->with(['sender', 'schoolClass'])
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->paginate(30);

        return view('platform.tenant-messages.index', compact('tenant', 'messages'));
    }
}
