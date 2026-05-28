<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationLog extends BaseModel
{
    public const CHANNEL_SMS = 'sms';

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const CHANNEL_IN_APP = 'in_app';

    public const CHANNELS = [
        self::CHANNEL_SMS,
        self::CHANNEL_EMAIL,
        self::CHANNEL_WHATSAPP,
        self::CHANNEL_IN_APP,
    ];

    public const PURPOSE_FEE_REMINDER = 'fee_reminder';

    public const PURPOSE_ATTENDANCE_ALERT = 'attendance_alert';

    public const PURPOSE_RESULT_NOTICE = 'result_notice';

    public const PURPOSE_ANNOUNCEMENT = 'announcement';

    public const PURPOSE_GENERAL = 'general';

    public const PURPOSES = [
        self::PURPOSE_FEE_REMINDER,
        self::PURPOSE_ATTENDANCE_ALERT,
        self::PURPOSE_RESULT_NOTICE,
        self::PURPOSE_ANNOUNCEMENT,
        self::PURPOSE_GENERAL,
    ];

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'tenant_id',
        'recipient_user_id',
        'recipient_phone',
        'recipient_email',
        'channel',
        'purpose',
        'subject',
        'message',
        'status',
        'provider',
        'provider_reference',
        'error_message',
        'sent_at',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
