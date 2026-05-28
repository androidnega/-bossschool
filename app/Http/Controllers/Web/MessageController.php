<?php

namespace App\Http\Controllers\Web;

use App\Enums\MessageRecipientType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Models\Message;
use App\Services\CommunicationLogger;
use App\Services\Sms\SmsDispatcher;
use App\Models\CommunicationLog;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Message::class);

        $user = request()->user();
        $query = Message::query()->with(['sender', 'schoolClass']);

        if ($user->role === UserRole::Teacher->value) {
            $query->visibleToTeacher($user);
        }

        $messages = $query->paginate(25);

        $collection = $messages->getCollection();
        $userIds = $collection->filter(fn (Message $m) => $m->recipient_type === User::class)->pluck('recipient_id')->filter()->unique()->values();
        $studentIds = $collection->filter(fn (Message $m) => $m->recipient_type === Student::class)->pluck('recipient_id')->filter()->unique()->values();
        if ($userIds->isNotEmpty()) {
            $usersById = User::query()->whereIn('id', $userIds)->get()->keyBy('id');
            foreach ($collection as $m) {
                if ($m->recipient_type === User::class && $m->recipient_id && isset($usersById[$m->recipient_id])) {
                    $m->setRelation('recipientUser', $usersById[$m->recipient_id]);
                }
            }
        }
        if ($studentIds->isNotEmpty()) {
            $studentsById = Student::query()->whereIn('id', $studentIds)->get()->keyBy('id');
            foreach ($collection as $m) {
                if ($m->recipient_type === Student::class && $m->recipient_id && isset($studentsById[$m->recipient_id])) {
                    $m->setRelation('recipientStudent', $studentsById[$m->recipient_id]);
                }
            }
        }
        $messages->setCollection($collection);

        $classes = SchoolClass::query()->orderBy('name')->get();
        $parentUsers = User::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('role', UserRole::Parent->value)
            ->orderBy('name')
            ->get();

        $canCreateSchool = $user->can('create', Message::class);
        $canFeeReminder = $user->can('sendFeeReminder', Message::class);
        $canClassNotice = $user->can('sendClassNotice', Message::class);

        return view('messages.index', compact(
            'messages',
            'classes',
            'parentUsers',
            'canCreateSchool',
            'canFeeReminder',
            'canClassNotice'
        ));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('messages.index');
    }

    public function store(StoreMessageRequest $request, CommunicationLogger $commLogger, SmsDispatcher $smsDispatcher): RedirectResponse
    {
        $user = $request->user();
        $type = (string) $request->validated('recipient_type');
        $classId = $request->filled('school_class_id') ? (int) $request->validated('school_class_id') : null;
        $recipientUserId = $request->filled('recipient_user_id') ? (int) $request->validated('recipient_user_id') : null;

        $channel = match ($user->role) {
            UserRole::Accountant->value => Message::CHANNEL_FEE_REMINDER,
            UserRole::Teacher->value => Message::CHANNEL_CLASS_NOTICE,
            default => Message::CHANNEL_SCHOOL_NOTICE,
        };

        $audience = $this->buildAudienceLabel($type, $classId, $recipientUserId);

        $recipientId = null;
        if ($type === MessageRecipientType::SelectedParent->value) {
            $recipientId = $recipientUserId;
        }

        $message = Message::query()->create([
            'sender_id' => $user->id,
            'title' => $request->validated('title'),
            'audience' => $audience,
            'recipient_type' => $type,
            'recipient_id' => $recipientId,
            'school_class_id' => $classId,
            'channel' => 'in_app',
            'notice_kind' => $channel,
            'content' => $request->validated('content'),
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        if ($channel === Message::CHANNEL_FEE_REMINDER) {
            $logs = $commLogger->recordMessage($message);

            if ($request->boolean('send_sms', false)) {
                $logs
                    ->where('channel', CommunicationLog::CHANNEL_SMS)
                    ->where('status', CommunicationLog::STATUS_QUEUED)
                    ->each(fn (CommunicationLog $log) => $smsDispatcher->dispatch($log));
            }
        }

        return redirect()->route('messages.index')->with('status', __('Notice sent.'));
    }

    private function buildAudienceLabel(string $type, ?int $classId, ?int $recipientUserId): string
    {
        $enum = MessageRecipientType::tryFromString($type);
        $base = $enum?->label() ?? $type;
        if ($classId) {
            $name = SchoolClass::query()->whereKey($classId)->value('name');

            return $name ? $base.' · '.$name : $base;
        }
        if ($recipientUserId) {
            $name = User::query()->whereKey($recipientUserId)->value('name');

            return $name ? $base.' · '.$name : $base;
        }

        return $base;
    }
}
