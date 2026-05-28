<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SuperAdmin support inbox: every tenant's tickets in one queue.
 */
class SupportInboxController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('platform.manageTenants');

        $query = SupportTicket::query()
            ->with('tenant', 'creator', 'assignee')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderByDesc('created_at');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($category = $request->string('category')->toString()) {
            $query->where('category', $category);
        }

        $tickets = $query->paginate(25)->withQueryString();

        return view('platform.support.index', [
            'tickets' => $tickets,
            'statuses' => SupportTicket::STATUSES,
            'categories' => SupportTicket::CATEGORIES,
            'filters' => $request->only(['status', 'category']),
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        $this->authorize('platform.manageTenants');

        return view('platform.support.show', [
            'ticket' => $ticket->load('messages.author', 'attachments', 'creator', 'tenant'),
        ]);
    }
}
