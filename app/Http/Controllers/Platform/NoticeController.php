<?php

namespace App\Http\Controllers\Platform;

use App\Enums\MessageRecipientType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StorePlatformNoticeRequest;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewPlatformNotices');

        $notices = Message::query()
            ->platformNotices()
            ->with('sender')
            ->orderByDesc('sent_at')
            ->limit(50)
            ->get();

        return view('platform.notices.index', compact('notices'));
    }

    public function store(StorePlatformNoticeRequest $request): RedirectResponse
    {
        $this->authorize('sendPlatformNotice');

        Message::query()->withoutGlobalScopes()->create([
            'tenant_id' => null,
            'sender_id' => $request->user()->id,
            'title' => $request->validated('title'),
            'audience' => MessageRecipientType::PlatformTenants->label(),
            'recipient_type' => MessageRecipientType::PlatformTenants->value,
            'recipient_id' => null,
            'school_class_id' => null,
            'channel' => 'platform',
            'notice_kind' => Message::CHANNEL_PLATFORM,
            'content' => $request->validated('content'),
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return redirect()->route('platform.notices.index')->with('status', __('Platform notice published.'));
    }
}
