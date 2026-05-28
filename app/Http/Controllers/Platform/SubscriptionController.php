<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('platform.manage');

        $subscriptions = Subscription::query()
            ->withoutGlobalScopes()
            ->with(['tenant', 'plan'])
            ->orderByDesc('end_date')
            ->paginate(30);

        return view('platform.subscriptions', compact('subscriptions'));
    }
}
