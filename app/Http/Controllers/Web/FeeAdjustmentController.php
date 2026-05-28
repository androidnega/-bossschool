<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreFeeAdjustmentRequest;
use App\Models\FeeAdjustment;
use App\Models\FeeInvoice;
use App\Models\Student;
use App\Services\ActivityLogger;
use App\Services\InvoiceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeAdjustmentController extends Controller
{
    public function __construct(
        private readonly InvoiceCalculator $calculator,
        private readonly ActivityLogger $logger,
    ) {
        $this->authorizeResource(FeeAdjustment::class, 'feeAdjustment', [
            'except' => ['approve', 'reject'],
        ]);
    }

    public function index(Request $request): View
    {
        $query = FeeAdjustment::query()
            ->with(['student', 'invoice', 'creator', 'approver'])
            ->orderByDesc('id');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $adjustments = $query->paginate(20)->withQueryString();

        return view('fee-adjustments.index', compact('adjustments'));
    }

    public function create(Request $request): View
    {
        $students = Student::query()->orderBy('name')->get();
        $invoices = FeeInvoice::query()
            ->whereIn('status', [FeeInvoice::STATUS_ISSUED, FeeInvoice::STATUS_PARTIALLY_PAID])
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('fee-adjustments.create', compact('students', 'invoices'));
    }

    public function store(StoreFeeAdjustmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = (int) $request->user()->tenant_id;
        $data['created_by_user_id'] = (int) $request->user()->id;
        $data['status'] = FeeAdjustment::STATUS_PENDING;

        $adjustment = FeeAdjustment::query()->create($data);

        return redirect()->route('fee-adjustments.index')
            ->with('status', __('Adjustment request created. Awaiting approval.'));
    }

    public function destroy(Request $request, FeeAdjustment $feeAdjustment): RedirectResponse
    {
        $this->authorize('cancel', $feeAdjustment);

        if ($feeAdjustment->status === FeeAdjustment::STATUS_APPROVED) {
            $feeAdjustment->update([
                'status' => FeeAdjustment::STATUS_CANCELLED,
                'decided_at' => now(),
                'approved_by_user_id' => (int) $request->user()->id,
                'decision_notes' => trim((string) $feeAdjustment->decision_notes."\n[".now()->toDateString().'] '.__('Cancelled by ').$request->user()->name),
            ]);

            if ($feeAdjustment->fee_invoice_id) {
                $invoice = FeeInvoice::query()->find($feeAdjustment->fee_invoice_id);
                if ($invoice) {
                    $this->calculator->refresh($invoice);
                }
            }
        } else {
            $feeAdjustment->update([
                'status' => FeeAdjustment::STATUS_CANCELLED,
                'decided_at' => now(),
            ]);
        }

        $this->logger->log(
            'fee_adjustment_cancelled',
            __('Fee adjustment cancelled'),
            ['adjustment_id' => $feeAdjustment->id],
            (int) $feeAdjustment->tenant_id,
            FeeAdjustment::class,
            (int) $feeAdjustment->id,
        );

        return redirect()->route('fee-adjustments.index')->with('status', __('Adjustment cancelled.'));
    }

    public function approve(Request $request, FeeAdjustment $feeAdjustment): RedirectResponse
    {
        $this->authorize('decide', $feeAdjustment);

        if ($feeAdjustment->status !== FeeAdjustment::STATUS_PENDING) {
            return back()->withErrors(['status' => __('Only pending adjustments can be approved.')]);
        }

        $feeAdjustment->update([
            'status' => FeeAdjustment::STATUS_APPROVED,
            'approved_by_user_id' => (int) $request->user()->id,
            'decided_at' => now(),
            'decision_notes' => $request->input('notes'),
        ]);

        if ($feeAdjustment->fee_invoice_id) {
            $invoice = FeeInvoice::query()->find($feeAdjustment->fee_invoice_id);
            if ($invoice) {
                $this->calculator->refresh($invoice);
            }
        }

        $this->logger->log(
            'fee_adjustment_approved',
            __('Fee adjustment approved'),
            ['adjustment_id' => $feeAdjustment->id, 'amount' => (float) $feeAdjustment->amount, 'type' => $feeAdjustment->type],
            (int) $feeAdjustment->tenant_id,
            FeeAdjustment::class,
            (int) $feeAdjustment->id,
        );

        return back()->with('status', __('Adjustment approved.'));
    }

    public function reject(Request $request, FeeAdjustment $feeAdjustment): RedirectResponse
    {
        $this->authorize('decide', $feeAdjustment);

        if ($feeAdjustment->status !== FeeAdjustment::STATUS_PENDING) {
            return back()->withErrors(['status' => __('Only pending adjustments can be rejected.')]);
        }

        $data = $request->validate([
            'notes' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $feeAdjustment->update([
            'status' => FeeAdjustment::STATUS_REJECTED,
            'approved_by_user_id' => (int) $request->user()->id,
            'decided_at' => now(),
            'decision_notes' => $data['notes'],
        ]);

        $this->logger->log(
            'fee_adjustment_rejected',
            __('Fee adjustment rejected'),
            ['adjustment_id' => $feeAdjustment->id, 'reason' => $data['notes']],
            (int) $feeAdjustment->tenant_id,
            FeeAdjustment::class,
            (int) $feeAdjustment->id,
        );

        return back()->with('status', __('Adjustment rejected.'));
    }
}
