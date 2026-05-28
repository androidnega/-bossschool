<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Support\ReplySupportTicketRequest;
use App\Http\Requests\Support\StoreSupportTicketRequest;
use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\SupportTicketMessage;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * School-facing support tickets. Tenant users see only their tenant's
 * tickets. Closed tickets are read-only.
 */
class SupportTicketController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupportTicket::class);
        $user = $request->user();

        $query = SupportTicket::query()
            ->with('creator', 'assignee')
            ->where('tenant_id', $user->tenant_id)
            ->orderByDesc('created_at');

        if (! in_array((string) $user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true)) {
            $query->where('created_by_user_id', $user->id);
        }

        $tickets = $query->paginate(20);

        return view('support.index', compact('tickets'));
    }

    public function create(): View
    {
        $this->authorize('create', SupportTicket::class);

        return view('support.create', [
            'categories' => SupportTicket::CATEGORIES,
            'priorities' => SupportTicket::PRIORITIES,
        ]);
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $ticket = DB::transaction(function () use ($data, $user, $request) {
            $ticket = SupportTicket::query()->create([
                'tenant_id' => $user->tenant_id,
                'created_by_user_id' => $user->id,
                'subject' => $data['subject'],
                'body' => $data['body'],
                'category' => $data['category'],
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'open',
            ]);

            if ($request->hasFile('attachment')) {
                $this->storeAttachment($ticket, $request->file('attachment'), $user->id);
            }

            return $ticket;
        });

        $this->logger->log(
            'support_ticket_created',
            'Support ticket opened',
            ['ticket_id' => $ticket->id, 'category' => $ticket->category, 'priority' => $ticket->priority],
            $user->tenant_id,
            SupportTicket::class,
            $ticket->id
        );

        return redirect()->route('support.show', $ticket)->with('status', __('Support ticket created.'));
    }

    public function show(SupportTicket $ticket): View
    {
        $this->authorize('view', $ticket);

        return view('support.show', [
            'ticket' => $ticket->load('messages.author', 'attachments', 'creator'),
            'can_change_status' => auth()->user()->can('changeStatus', $ticket),
        ]);
    }

    public function reply(ReplySupportTicketRequest $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $isInternal = (bool) ($data['is_internal_note'] ?? false);

        if ($isInternal && ! $user->can('addInternalNote', $ticket)) {
            abort(403);
        }

        DB::transaction(function () use ($ticket, $data, $user, $isInternal, $request) {
            SupportTicketMessage::query()->create([
                'support_ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => $data['body'],
                'is_internal_note' => $isInternal,
            ]);

            if ($request->hasFile('attachment')) {
                $this->storeAttachment($ticket, $request->file('attachment'), $user->id);
            }

            if ($ticket->status === 'pending' && ! $user->isSuperAdmin()) {
                $ticket->forceFill(['status' => 'open'])->save();
            }
        });

        return back()->with('status', __('Reply added.'));
    }

    public function changeStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('changeStatus', $ticket);
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', SupportTicket::STATUSES)],
        ]);
        $before = $ticket->status;
        $ticket->forceFill([
            'status' => $data['status'],
            'resolved_at' => $data['status'] === 'resolved' ? now() : $ticket->resolved_at,
            'closed_at' => $data['status'] === 'closed' ? now() : $ticket->closed_at,
        ])->save();

        $this->logger->log(
            'support_ticket_status_changed',
            "Support ticket status: {$before} -> {$data['status']}",
            ['ticket_id' => $ticket->id, 'from' => $before, 'to' => $data['status']],
            $ticket->tenant_id,
            SupportTicket::class,
            $ticket->id
        );

        return back()->with('status', __('Ticket status updated.'));
    }

    public function downloadAttachment(SupportTicketAttachment $attachment): StreamedResponse|Response
    {
        $ticket = $attachment->ticket;
        $this->authorize('downloadAttachment', $ticket);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    private function storeAttachment(SupportTicket $ticket, $file, int $userId): void
    {
        $disk = (string) config('backups.disk', 'local');
        $path = Storage::disk($disk)->putFile('support/'.$ticket->id, $file);

        SupportTicketAttachment::query()->create([
            'support_ticket_id' => $ticket->id,
            'uploaded_by_user_id' => $userId,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => (int) $file->getSize(),
        ]);
    }
}
