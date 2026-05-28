<?php

namespace App\Services;

use App\Models\CommunicationLog;
use App\Models\Message;
use App\Models\Student;
use App\Models\User;

/**
 * Writes structured communication_logs rows. This is the seam where a future
 * SMS / WhatsApp / email gateway will plug in. For now every entry is
 * persisted with status = queued (or skipped if the channel cannot reach the
 * recipient — e.g. SMS but no phone number on file).
 */
class CommunicationLogger
{
    /**
     * Mirror an in-app Message into the communication log so audit + future
     * provider hand-off works consistently. Only writes fee-reminder type
     * messages today; other message kinds can opt in later.
     *
     * @return \Illuminate\Support\Collection<int, CommunicationLog>
     */
    public function recordMessage(Message $message): \Illuminate\Support\Collection
    {
        if ($message->notice_kind !== Message::CHANNEL_FEE_REMINDER) {
            return collect();
        }

        $tenantId = (int) ($message->sender?->tenant_id ?? auth()->user()?->tenant_id ?? 0);
        $recipients = $this->resolveParentRecipients($message);

        if ($recipients->isEmpty()) {
            $row = CommunicationLog::query()->create([
                'tenant_id' => $tenantId,
                'channel' => CommunicationLog::CHANNEL_IN_APP,
                'purpose' => CommunicationLog::PURPOSE_FEE_REMINDER,
                'subject' => $message->title,
                'message' => $message->content,
                'status' => CommunicationLog::STATUS_SKIPPED,
                'error_message' => 'No identifiable parent recipients',
                'created_by_user_id' => $message->sender_id,
            ]);

            return collect([$row]);
        }

        $created = collect();
        foreach ($recipients as $parent) {
            $phone = $this->resolveParentPhone($parent);
            $hasPhone = ! empty($phone);
            $created->push(CommunicationLog::query()->create([
                'tenant_id' => $tenantId,
                'recipient_user_id' => $parent->id,
                'recipient_phone' => $phone,
                'recipient_email' => $parent->email,
                'channel' => CommunicationLog::CHANNEL_SMS,
                'purpose' => CommunicationLog::PURPOSE_FEE_REMINDER,
                'subject' => $message->title,
                'message' => $message->content,
                'status' => $hasPhone ? CommunicationLog::STATUS_QUEUED : CommunicationLog::STATUS_SKIPPED,
                'error_message' => $hasPhone ? null : 'No phone number on file',
                'created_by_user_id' => $message->sender_id,
            ]));
        }

        return $created;
    }

    /**
     * Phone numbers don't live on the users table; they live on the linked
     * Student.parent_phone field. Resolve the first non-empty one for SMS
     * delivery (the existing Ghana phone normaliser already canonicalised
     * the value on save).
     */
    private function resolveParentPhone(User $parent): ?string
    {
        $student = $parent->children()->whereNotNull('parent_phone')->first();

        return $student?->parent_phone;
    }

    /**
     * Generic helper for outbound logs (used by future provider integrations
     * and the test suite). Returns the row so callers can react to it.
     */
    public function log(array $attributes): CommunicationLog
    {
        $attributes['tenant_id'] = $attributes['tenant_id'] ?? (int) (auth()->user()->tenant_id ?? 0);
        $attributes['status'] = $attributes['status'] ?? CommunicationLog::STATUS_QUEUED;
        $attributes['channel'] = $attributes['channel'] ?? CommunicationLog::CHANNEL_IN_APP;
        $attributes['purpose'] = $attributes['purpose'] ?? CommunicationLog::PURPOSE_GENERAL;

        return CommunicationLog::query()->create($attributes);
    }

    private function resolveParentRecipients(Message $message): \Illuminate\Support\Collection
    {
        $tenantId = (int) ($message->sender?->tenant_id ?? auth()->user()?->tenant_id ?? 0);

        $parentQuery = User::query()->where('tenant_id', $tenantId)->where('role', \App\Enums\UserRole::Parent->value);

        if ($message->recipient_type === User::class && $message->recipient_id) {
            return $parentQuery->whereKey($message->recipient_id)->get();
        }

        if ($message->school_class_id) {
            $studentIds = Student::query()
                ->where('tenant_id', $tenantId)
                ->where('class_id', $message->school_class_id)
                ->pluck('id');

            if ($studentIds->isEmpty()) {
                return collect();
            }

            return $parentQuery
                ->whereHas('children', fn ($q) => $q->whereIn('students.id', $studentIds))
                ->get();
        }

        return $parentQuery->get();
    }
}
