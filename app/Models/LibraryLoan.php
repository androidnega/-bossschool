<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryLoan extends BaseModel
{
    use RecordsAuditTrail;

    public const STATUS_BORROWED = 'borrowed';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_LOST = 'lost';

    public const STATUSES = [
        self::STATUS_BORROWED,
        self::STATUS_RETURNED,
        self::STATUS_OVERDUE,
        self::STATUS_LOST,
    ];

    protected $fillable = [
        'tenant_id',
        'library_book_id',
        'student_id',
        'staff_id',
        'borrowed_at',
        'due_at',
        'returned_at',
        'fine_amount',
        'status',
        'issued_by_user_id',
        'received_by_user_id',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'date:Y-m-d',
            'due_at' => 'date:Y-m-d',
            'returned_at' => 'date:Y-m-d',
            'fine_amount' => 'decimal:2',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'library_book_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
