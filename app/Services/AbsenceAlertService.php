<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\CommunicationLog;
use App\Models\SchoolClass;
use App\Services\Sms\SmsDispatcher;
use Illuminate\Database\Eloquent\Collection;

/**
 * Queue (and optionally dispatch) SMS absence alerts to parents for a given
 * day's attendance rows. Callers can choose whether to actually attempt a
 * send or just create queued rows. Run from controller, command, or a
 * future scheduler.
 */
class AbsenceAlertService
{
    public function __construct(private readonly SmsDispatcher $sms) {}

    /**
     * Create a queued log row per absent student for the given attendance
     * rows. `$send=true` immediately dispatches each one through the
     * configured SMS provider; otherwise rows stay queued for later.
     *
     * @param  Collection<int, Attendance>  $rows
     * @return array{queued:int, sent:int}
     */
    public function alertAbsences(Collection $rows, bool $send = false): array
    {
        $queued = 0;
        $sent = 0;

        foreach ($rows as $row) {
            if ($row->status !== Attendance::STATUS_ABSENT) {
                continue;
            }

            $student = $row->student ?? $row->student()->first();
            if (! $student) {
                continue;
            }

            $phone = (string) ($student->parent_phone ?? '');

            $message = trans('Dear Parent, your child :name was marked absent on :date.', [
                'name' => $student->name,
                'date' => $row->date?->format('Y-m-d'),
            ]);

            $log = CommunicationLog::query()->create([
                'tenant_id' => $row->tenant_id,
                'recipient_phone' => $phone !== '' ? $phone : null,
                'channel' => CommunicationLog::CHANNEL_SMS,
                'purpose' => CommunicationLog::PURPOSE_ATTENDANCE_ALERT,
                'subject' => 'Absence alert',
                'message' => $message,
                'status' => $phone !== '' ? CommunicationLog::STATUS_QUEUED : CommunicationLog::STATUS_SKIPPED,
                'error_message' => $phone === '' ? 'No parent phone on file' : null,
                'created_by_user_id' => $row->marked_by_user_id,
            ]);
            $queued++;

            if ($send && $log->status === CommunicationLog::STATUS_QUEUED) {
                $this->sms->dispatch($log);
                if ($log->status === CommunicationLog::STATUS_SENT) {
                    $sent++;
                }
            }
        }

        return ['queued' => $queued, 'sent' => $sent];
    }

    /**
     * Queue + send absence alerts for every absent student in a given class
     * on a specific date. Wrapped by SendAbsenceAlertsJob.
     *
     * @return array{queued:int, sent:int}
     */
    public function dispatchForClassDate(SchoolClass $class, string $date): array
    {
        $rows = Attendance::query()
            ->with('student')
            ->where('tenant_id', $class->tenant_id)
            ->where('class_id', $class->id)
            ->whereDate('date', $date)
            ->where('status', Attendance::STATUS_ABSENT)
            ->get();

        return $this->alertAbsences($rows, send: true);
    }
}
