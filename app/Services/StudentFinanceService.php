<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Support\Collection;

/**
 * Centralised view of a single student's balance, arrears, invoices, and
 * payment history. Used by the parent portal, student portal, invoice page,
 * statement page, and debtor reports so they all show the same numbers.
 */
class StudentFinanceService
{
    /**
     * Build a full statement payload for a student.
     *
     * @return array{
     *   student: Student,
     *   invoices: \Illuminate\Database\Eloquent\Collection<int,\App\Models\FeeInvoice>,
     *   payments: \Illuminate\Database\Eloquent\Collection<int,\App\Models\Payment>,
     *   adjustments: \Illuminate\Database\Eloquent\Collection<int,\App\Models\FeeAdjustment>,
     *   totals: array{
     *     billed: float,
     *     discounts: float,
     *     waivers: float,
     *     paid: float,
     *     balance: float,
     *     arrears: float,
     *   },
     * }
     */
    public function statement(Student $student): array
    {
        $tenantId = (int) $student->tenant_id;

        $invoices = FeeInvoice::query()
            ->with(['items', 'academicYear', 'term', 'schoolClass'])
            ->where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->orderBy('issued_at')
            ->orderBy('id')
            ->get();

        $payments = Payment::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $adjustments = \App\Models\FeeAdjustment::query()
            ->with(['approver', 'creator'])
            ->where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->orderByDesc('id')
            ->get();

        $billed = (float) $invoices->whereIn('status', FeeInvoice::ACTIVE_STATUSES)->sum('subtotal');
        $discounts = (float) $invoices->whereIn('status', FeeInvoice::ACTIVE_STATUSES)->sum('discount_total');
        $waivers = (float) $invoices->whereIn('status', FeeInvoice::ACTIVE_STATUSES)->sum('waiver_total');
        $paid = (float) $payments->where('status', Payment::STATUS_SUCCESSFUL)->sum('amount');
        $balance = (float) $invoices->whereIn('status', FeeInvoice::ACTIVE_STATUSES)->sum('balance');
        $arrears = $this->computeArrears($invoices);

        return [
            'student' => $student,
            'invoices' => $invoices,
            'payments' => $payments,
            'adjustments' => $adjustments,
            'totals' => [
                'billed' => round($billed, 2),
                'discounts' => round($discounts, 2),
                'waivers' => round($waivers, 2),
                'paid' => round($paid, 2),
                'balance' => round($balance, 2),
                'arrears' => round($arrears, 2),
            ],
        ];
    }

    /**
     * Arrears are unpaid balances from invoices whose academic year/term is
     * earlier than the current open period. Computed from the invoice ledger
     * so we never duplicate them as fake payments. When excludeInvoiceId is
     * provided, that invoice is skipped (useful on the invoice show page,
     * where we want "other unpaid invoices" specifically).
     */
    public function arrearsForStudent(int $tenantId, int $studentId, ?int $excludeInvoiceId = null): float
    {
        $current = $this->currentPeriodCutoff();

        return (float) FeeInvoice::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $studentId)
            ->whereIn('status', FeeInvoice::ACTIVE_STATUSES)
            ->when($excludeInvoiceId, fn ($q) => $q->where('id', '!=', $excludeInvoiceId))
            ->when($current['date'], function ($q) use ($current): void {
                $q->where(function ($q) use ($current): void {
                    $q->whereNull('issued_at')
                        ->orWhere('issued_at', '<', $current['date']);
                });
            })
            ->sum('balance');
    }

    /**
     * @param  Collection<int, FeeInvoice>  $invoices
     */
    private function computeArrears(Collection $invoices): float
    {
        $cutoff = $this->currentPeriodCutoff();
        if (! $cutoff['date']) {
            return 0.0;
        }

        return (float) $invoices
            ->filter(fn (FeeInvoice $i): bool => in_array($i->status, FeeInvoice::ACTIVE_STATUSES, true))
            ->filter(function (FeeInvoice $i) use ($cutoff): bool {
                if (! $i->issued_at) {
                    return true; // ledger row without a date — treat as pre-current
                }

                return $i->issued_at->lt($cutoff['date']);
            })
            ->sum(fn (FeeInvoice $i): float => (float) $i->balance);
    }

    /**
     * @return array{date: ?\Carbon\Carbon}
     */
    private function currentPeriodCutoff(): array
    {
        // We treat the start of the current term (or current academic year,
        // if no term is current) as the boundary between arrears and
        // current-period balance.
        $term = Term::query()->where('is_current', true)->first();
        if ($term && $term->starts_on) {
            return ['date' => \Carbon\Carbon::parse($term->starts_on)];
        }

        $year = AcademicYear::query()->where('is_current', true)->first();
        if ($year && $year->starts_on) {
            return ['date' => \Carbon\Carbon::parse($year->starts_on)];
        }

        return ['date' => null];
    }
}
