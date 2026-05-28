<?php

namespace App\Services;

use App\Models\FeeAdjustment;
use App\Models\FeeInvoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for invoice totals.
 *
 * Anything that touches an invoice (items added/edited, adjustments
 * approved/rejected, payments recorded/reversed) should call
 * {@see refresh()} so the rollup columns stay in lock-step with the
 * underlying ledger rows. Trying to maintain totals inline scatters the
 * logic and makes audits painful — keep it here.
 */
class InvoiceCalculator
{
    /**
     * Recompute totals from the underlying rows. Returns the refreshed model.
     *
     * - subtotal       = SUM(items.total_amount)
     * - discount_total = SUM(approved adjustments WHERE type=discount)
     * - waiver_total   = SUM(approved adjustments WHERE type IN (waiver, scholarship))
     * - amount_due     = max(0, subtotal - discount_total - waiver_total)
     * - amount_paid    = SUM(successful payments)
     * - balance        = max(0, amount_due - amount_paid)
     * - status flips between issued / partially_paid / paid (unless the
     *   invoice is currently in a frozen state like draft/cancelled).
     */
    public function refresh(FeeInvoice $invoice): FeeInvoice
    {
        return DB::transaction(function () use ($invoice): FeeInvoice {
            $subtotal = (float) $invoice->items()->sum('total_amount');

            $discountTotal = (float) FeeAdjustment::query()
                ->where('fee_invoice_id', $invoice->id)
                ->where('type', FeeAdjustment::TYPE_DISCOUNT)
                ->where('status', FeeAdjustment::STATUS_APPROVED)
                ->sum('amount');

            $waiverTotal = (float) FeeAdjustment::query()
                ->where('fee_invoice_id', $invoice->id)
                ->whereIn('type', [FeeAdjustment::TYPE_WAIVER, FeeAdjustment::TYPE_SCHOLARSHIP])
                ->where('status', FeeAdjustment::STATUS_APPROVED)
                ->sum('amount');

            $paid = (float) Payment::query()
                ->where('fee_invoice_id', $invoice->id)
                ->where('status', Payment::STATUS_SUCCESSFUL)
                ->sum('amount');

            $amountDue = max(0.0, round($subtotal - $discountTotal - $waiverTotal, 2));
            $balance = max(0.0, round($amountDue - $paid, 2));

            $invoice->subtotal = $subtotal;
            $invoice->discount_total = $discountTotal;
            $invoice->waiver_total = $waiverTotal;
            $invoice->amount_due = $amountDue;
            $invoice->amount_paid = $paid;
            $invoice->balance = $balance;

            // Only auto-flip status if the invoice is currently in a state
            // that should react to financial activity. Draft + cancelled
            // invoices stay where they are.
            if (in_array($invoice->status, FeeInvoice::ACTIVE_STATUSES, true)) {
                if ($amountDue <= 0.0 || $balance <= 0.0) {
                    $invoice->status = FeeInvoice::STATUS_PAID;
                } elseif ($paid > 0.0) {
                    $invoice->status = FeeInvoice::STATUS_PARTIALLY_PAID;
                } else {
                    $invoice->status = FeeInvoice::STATUS_ISSUED;
                }
            }

            $invoice->save();

            return $invoice->refresh();
        });
    }
}
