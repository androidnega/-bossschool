<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends BaseModel
{
    use RecordsAuditTrail;

    public const TYPE_RECEIVE = 'receive';

    public const TYPE_ISSUE = 'issue';

    public const TYPE_ADJUST = 'adjust';

    public const TYPE_RETURN = 'return';

    public const TYPES = [
        self::TYPE_RECEIVE,
        self::TYPE_ISSUE,
        self::TYPE_ADJUST,
        self::TYPE_RETURN,
    ];

    protected $fillable = [
        'tenant_id',
        'inventory_item_id',
        'movement_type',
        'quantity',
        'unit_cost',
        'reason',
        'performed_by_user_id',
        'related_student_id',
        'related_staff_id',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}
