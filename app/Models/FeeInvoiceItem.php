<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeInvoiceItem extends BaseModel
{
    use RecordsAuditTrail;

    /**
     * Suggested categories. Free-text categories are still allowed but the
     * UI offers these defaults to keep reports + statements consistent.
     *
     * @var array<int, string>
     */
    public const SUGGESTED_CATEGORIES = [
        'tuition',
        'feeding',
        'transport',
        'uniform',
        'books',
        'exam',
        'pta',
        'extra_tuition',
        'arrears_adjustment',
        'other',
    ];

    protected $fillable = [
        'tenant_id',
        'fee_invoice_id',
        'fee_id',
        'description',
        'category',
        'quantity',
        'unit_amount',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function feeTemplate(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'fee_id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $qty = max(1, (int) ($item->quantity ?? 1));
            $unit = (float) ($item->unit_amount ?? 0);
            $item->quantity = $qty;
            $item->total_amount = round($qty * $unit, 2);
        });
    }
}
