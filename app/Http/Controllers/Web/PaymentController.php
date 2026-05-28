<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Models\Student;
use App\Services\ActivityLogger;
use App\Services\InvoiceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Payment::class, 'payment', [
            'except' => ['create', 'store', 'edit', 'update', 'reverse'],
        ]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Payment::query()
            ->with(['student.schoolClass', 'invoice'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($user->role === UserRole::Parent->value) {
            $query->whereIn('student_id', $user->children()->pluck('students.id'));
        } elseif ($user->role === UserRole::Student->value) {
            $query->where('student_id', $user->student_id);
        }

        $payments = $query->paginate(20);

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Payment::class);

        $students = Student::query()->with('schoolClass')->orderBy('name')->get();

        // Optional preselect via ?invoice_id=... so accountants can record a
        // payment directly from an invoice page.
        $invoice = null;
        if ($invoiceId = $request->query('invoice_id')) {
            $invoice = FeeInvoice::query()->find($invoiceId);
        }

        return view('payments.create', compact('students', 'invoice'));
    }

    public function store(StorePaymentRequest $request, InvoiceCalculator $calculator): RedirectResponse
    {
        $data = $request->validated();
        $data['receipt_id'] = $this->generateReceiptId();
        $data['received_by_user_id'] = (int) $request->user()->id;
        $data['status'] = Payment::STATUS_SUCCESSFUL;
        $data['provider'] = $data['provider'] ?? Payment::PROVIDER_MANUAL;

        // Normalise legacy aliases — store the canonical fields only.
        if (! empty($data['reference']) && empty($data['payment_reference'])) {
            $data['payment_reference'] = $data['reference'];
        }
        $data['reference'] = $data['payment_reference'] ?? $data['reference'] ?? null;

        $payment = Payment::query()->create($data);

        if ($payment->fee_invoice_id) {
            $invoice = FeeInvoice::query()->find($payment->fee_invoice_id);
            if ($invoice) {
                $calculator->refresh($invoice);
            }
        }

        return redirect()->route('payments.show', $payment)->with('status', __('Payment recorded.'));
    }

    public function show(Payment $payment): View
    {
        $payment->load(['student.schoolClass', 'invoice', 'receiver', 'reverser']);

        return view('payments.show', compact('payment'));
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        // Policy prevents hard delete unless the payment is already reversed.
        $payment->delete();

        return redirect()->route('payments.index')->with('status', __('Payment removed.'));
    }

    public function reverse(Request $request, Payment $payment, InvoiceCalculator $calculator, ActivityLogger $logger): RedirectResponse
    {
        $this->authorize('reverse', $payment);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
            'confirm' => ['required', Rule::in(['1', 1, true, 'true'])],
        ]);

        $payment->update([
            'status' => Payment::STATUS_REVERSED,
            'reversed_by_user_id' => (int) $request->user()->id,
            'reversed_at' => now(),
            'reversal_reason' => $data['reason'],
        ]);

        if ($payment->fee_invoice_id) {
            $invoice = FeeInvoice::query()->find($payment->fee_invoice_id);
            if ($invoice) {
                $calculator->refresh($invoice);
            }
        }

        $logger->log(
            'payment_reversed',
            __('Payment :receipt reversed', ['receipt' => $payment->receipt_id]),
            ['reason' => $data['reason']],
            (int) $payment->tenant_id,
            Payment::class,
            (int) $payment->id,
        );

        return redirect()->route('payments.show', $payment)->with('status', __('Payment reversed.'));
    }

    private function generateReceiptId(): string
    {
        do {
            $id = 'RCP-'.now()->format('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
        } while (Payment::query()->where('receipt_id', $id)->exists());

        return $id;
    }
}
