<?php

namespace App\Models;

use App\Enums\MessageRecipientType;
use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Message extends BaseModel
{
    use RecordsAuditTrail;

    public const CHANNEL_FEE_REMINDER = 'fee_reminder';

    public const CHANNEL_SCHOOL_NOTICE = 'school_notice';

    public const CHANNEL_CLASS_NOTICE = 'class_notice';

    public const CHANNEL_PLATFORM = 'platform';

    protected $fillable = [
        'tenant_id',
        'sender_id',
        'title',
        'audience',
        'recipient_type',
        'recipient_id',
        'school_class_id',
        'channel',
        'notice_kind',
        'content',
        'status',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function recipientTypeEnum(): ?MessageRecipientType
    {
        return MessageRecipientType::tryFromString($this->recipient_type);
    }

    public function audienceDisplay(): string
    {
        if ($this->audience) {
            return $this->audience;
        }

        $enum = $this->recipientTypeEnum();
        if ($enum) {
            $base = $enum->label();
            if ($this->school_class_id && $this->relationLoaded('schoolClass') && $this->schoolClass) {
                return $base.' · '.$this->schoolClass->name;
            }
            if ($this->school_class_id) {
                return $base.' · #'.$this->school_class_id;
            }

            return $base;
        }

        if ($this->recipient_type === User::class) {
            if ($this->relationLoaded('recipientUser') && $this->getRelation('recipientUser')) {
                /** @var User $u */
                $u = $this->getRelation('recipientUser');

                return __('Parent account: :name', ['name' => $u->name]);
            }

            return __('One parent (direct)');
        }

        if ($this->recipient_type === Student::class) {
            if ($this->relationLoaded('recipientStudent') && $this->getRelation('recipientStudent')) {
                /** @var Student $s */
                $s = $this->getRelation('recipientStudent');

                return __('Student: :name', ['name' => $s->name]);
            }

            return __('One student (direct)');
        }

        $type = (string) $this->recipient_type;
        if (str_contains($type, '\\')) {
            $short = Str::afterLast($type, '\\');
            if ($short === 'User') {
                return __('One parent (direct)');
            }
            if ($short === 'Student') {
                return __('One student (direct)');
            }
        }

        return __('Notice recipients');
    }

    /** Admin-facing label for notice category (never raw channel slugs). */
    public function noticeKindLabel(): string
    {
        $kind = $this->notice_kind;
        if ($kind) {
            return match ($kind) {
                self::CHANNEL_FEE_REMINDER => __('Fee reminder'),
                self::CHANNEL_CLASS_NOTICE => __('Class notice'),
                self::CHANNEL_SCHOOL_NOTICE => __('School notice'),
                self::CHANNEL_PLATFORM => __('Platform notice'),
                default => Str::headline(str_replace('_', ' ', (string) $kind)),
            };
        }

        $ch = strtolower((string) ($this->channel ?? ''));
        if ($ch === '' || $ch === 'in_app') {
            return __('In app');
        }
        if (str_contains($ch, 'fee') || str_contains($ch, 'balance') || str_contains($ch, 'bursary')) {
            return __('Fee reminder');
        }
        if (str_contains($ch, 'class') || preg_match('/\b(kg|p[0-9]|primary|jss|sss)\b/', $ch)) {
            return __('Class notice');
        }
        if (str_contains($ch, 'platform')) {
            return __('Platform notice');
        }

        return __('School notice');
    }

    public function displayTitle(): string
    {
        $t = trim((string) $this->title);
        if ($t !== '') {
            return $t;
        }
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->content)));
        if ($plain === '') {
            return __('Untitled notice');
        }

        return Str::limit($plain, 80);
    }

    public function statusDisplay(): string
    {
        return match ($this->status) {
            'sent' => __('Sent'),
            'draft' => __('Draft'),
            default => Str::headline((string) $this->status),
        };
    }

    public function scopePlatformNotices(Builder $query): Builder
    {
        return $query->withoutGlobalScopes()
            ->whereNull('tenant_id')
            ->where('recipient_type', MessageRecipientType::PlatformTenants->value);
    }

    public function scopeVisibleToTeacher(Builder $query, User $teacher): Builder
    {
        $classIds = $teacher->assignedClasses()->pluck('classes.id');

        return $query->where(function (Builder $q) use ($teacher, $classIds): void {
            $q->where('sender_id', $teacher->id)
                ->orWhereIn('recipient_type', [
                    MessageRecipientType::AllParents->value,
                    MessageRecipientType::AllStudents->value,
                    MessageRecipientType::AllUsers->value,
                    MessageRecipientType::Teachers->value,
                ])
                ->orWhere(function (Builder $q2) use ($classIds): void {
                    $q2->whereIn('recipient_type', [
                        MessageRecipientType::ClassParents->value,
                        MessageRecipientType::ClassStudents->value,
                    ])->whereIn('school_class_id', $classIds);
                });
        })->orderByDesc('sent_at');
    }

    public function scopeVisibleToParent(Builder $query, User $parent): Builder
    {
        $childIds = $parent->children()->pluck('students.id');
        $classIds = $parent->children()->pluck('students.class_id')->unique()->filter();

        return $query->where(function (Builder $q) use ($parent, $childIds, $classIds): void {
            $q->where('recipient_type', MessageRecipientType::AllParents->value)
                ->orWhere(function (Builder $q2) use ($classIds): void {
                    $q2->where('recipient_type', MessageRecipientType::ClassParents->value)
                        ->whereIn('school_class_id', $classIds);
                })
                ->orWhere(function (Builder $q2) use ($parent): void {
                    $q2->where('recipient_type', MessageRecipientType::SelectedParent->value)
                        ->where('recipient_id', $parent->id);
                })
                ->orWhere(function (Builder $q2) use ($classIds): void {
                    $q2->where('recipient_type', MessageRecipientType::ClassStudents->value)
                        ->whereIn('school_class_id', $classIds);
                })
                ->orWhere(function (Builder $q2) use ($classIds): void {
                    $q2->where('recipient_type', MessageRecipientType::AllStudents->value)
                        ->whereNotNull('school_class_id')
                        ->whereIn('school_class_id', $classIds);
                })
                ->orWhere(function (Builder $q2) use ($childIds): void {
                    $q2->where('recipient_type', Student::class)
                        ->whereIn('recipient_id', $childIds);
                })
                ->orWhere(function (Builder $q2) use ($parent): void {
                    $q2->where('recipient_type', User::class)
                        ->where('recipient_id', $parent->id);
                });
        })->orderByDesc('sent_at');
    }

    public function scopeVisibleToStudent(Builder $query, Student $student): Builder
    {
        $userId = User::query()->where('student_id', $student->id)->value('id');

        return $query->where(function (Builder $q) use ($student, $userId): void {
            $q->where('recipient_type', MessageRecipientType::AllStudents->value)
                ->orWhere(function (Builder $q2) use ($student): void {
                    $q2->where('recipient_type', MessageRecipientType::ClassStudents->value)
                        ->where('school_class_id', $student->class_id);
                })
                ->orWhere(function (Builder $q2) use ($student): void {
                    $q2->where('recipient_type', Student::class)
                        ->where('recipient_id', $student->id);
                });

            if ($userId) {
                $q->orWhere(function (Builder $q2) use ($userId): void {
                    $q2->where('recipient_type', User::class)
                        ->where('recipient_id', $userId);
                });
            }
        })->orderByDesc('sent_at');
    }

    public function scopeRecentForAdminDashboard(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('notice_kind')
                ->orWhere('notice_kind', '!=', self::CHANNEL_FEE_REMINDER);
        })->orderByDesc('sent_at');
    }

    public function scopeRecentForProprietorDashboard(Builder $query): Builder
    {
        return $query->orderByDesc('sent_at');
    }
}
