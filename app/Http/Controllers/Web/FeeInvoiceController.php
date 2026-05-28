<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreFeeInvoiceRequest;
use App\Http\Requests\Finance\UpdateFeeInvoiceRequest;
use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\School;
use App\Models\Student;
use App\Models\Term;
use App\Services\AcademicContext;
use App\Services\ActivityLogger;
use App\Services\InvoiceCalculator;
use App\Services\StudentFinanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class FeeInvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceCalculator $calculator,
        private readonly StudentFinanceService $finance,
        private readonly AcademicContext $academic,
        private readonly ActivityLogger $logger,
    ) {
        $this->authorizeResource(FeeInvoice::class, 'feeInvoice', [
            'except' => ['issue', 'cancel', 'pdf'],
        ]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $query = FeeInvoice::query()
            ->with(['student.schoolClass', 'term', 'academicYear'])
            ->orderByDesc('issued_at')
            ->orderByDesc('id');

        if ($user->role === UserRole::Parent->value) {
            $query->whereIn('student_id', $user->children()->pluck('students.id'));
        } elseif ($user->role === UserRole::Student->value) {
            $query->where('student_id', $user->student_id);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($studentId = $request->query('student_id')) {
            $query->where('student_id', (int) $studentId);
        }

        $invoices = $query->paginate(20)->withQueryString();

        $students = $user->isFinanceRole()
            ? Student::query()->orderBy('name')->get()
            : collect();

        return view('fee-invoices.index', compact('invoices', 'students'));
    }

    public function create(): View
    {
        $year = $this->academic->currentYear();
        $term = $this->academic->currentTerm();

        $students = Student::query()->with('schoolClass')->orderBy('name')->get();
        $years = AcademicYear::query()->orderByDesc('starts_on')->get();
        $terms = Term::query()->orderBy('term_order')->get();
        $classes = SchoolClass::query()->orderBy('name')->get();

        return view('fee-invoices.create', compact('students', 'years', 'terms', 'classes', 'year', 'term'));
    }

    public function store(StoreFeeInvoiceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tenantId = (int) $request->user()->tenant_id;
        $student = Student::query()->findOrFail($data['student_id']);

        $invoice = DB::transaction(function () use ($data, $tenantId, $student, $request): FeeInvoice {
            $invoice = FeeInvoice::query()->create([
                'tenant_id' => $tenantId,
                'student_id' => $student->id,
                'academic_year_id' => $data['academic_year_id'] ?? null,
                'term_id' => $data['term_id'] ?? null,
                'class_id' => $data['class_id'] ?? $student->class_id,
                'invoice_number' => $this->generateInvoiceNumber($tenantId),
                'status' => FeeInvoice::STATUS_DRAFT,
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by_user_id' => (int) $request->user()->id,
            ]);

            // Pre-populate from class fees as templates.
            if (! empty($data['use_class_template']) && $invoice->class_id) {
                $fees = Fee::query()
                    ->where('class_id', $invoice->class_id)
                    ->when($invoice->term_id, fn ($q) => $q->where('term_id', $invoice->term_id))
                    ->get();

                foreach ($fees as $fee) {
                    $invoice->items()->create([
                        'tenant_id' => $tenantId,
                        'fee_id' => $fee->id,
                        'description' => $fee->fee_type,
                        'category' => Str::slug(str_replace(' ', '_', $fee->fee_type), '_') ?: 'tuition',
                        'quantity' => 1,
                        'unit_amount' => $fee->amount,
                    ]);
                }
            }

            return $invoice;
        });

        $this->calculator->refresh($invoice);

        return redirect()->route('fee-invoices.show', $invoice)
            ->with('status', __('Invoice :n created.', ['n' => $invoice->invoice_number]));
    }

    public function show(FeeInvoice $feeInvoice): View
    {
        $feeInvoice->load(['student.schoolClass', 'items', 'payments', 'adjustments.approver', 'academicYear', 'term', 'creator']);
        $arrears = $this->finance->arrearsForStudent((int) $feeInvoice->tenant_id, (int) $feeInvoice->student_id, $feeInvoice->id);

        return view('fee-invoices.show', [
            'invoice' => $feeInvoice,
            'arrears' => $arrears,
        ]);
    }

    public function update(UpdateFeeInvoiceRequest $request, FeeInvoice $feeInvoice): RedirectResponse
    {
        $feeInvoice->update($request->validated());

        return redirect()->route('fee-invoices.show', $feeInvoice)
            ->with('status', __('Invoice updated.'));
    }

    public function destroy(FeeInvoice $feeInvoice): RedirectResponse
    {
        // Policy already enforces no-delete when paid. Use soft delete.
        $feeInvoice->delete();

        return redirect()->route('fee-invoices.index')
            ->with('status', __('Invoice removed.'));
    }

    public function issue(Request $request, FeeInvoice $feeInvoice): RedirectResponse
    {
        $this->authorize('update', $feeInvoice);

        if ($feeInvoice->status !== FeeInvoice::STATUS_DRAFT) {
            return back()->withErrors(['status' => __('Only draft invoices can be issued.')]);
        }

        $feeInvoice->update([
            'status' => FeeInvoice::STATUS_ISSUED,
            'issued_at' => now()->toDateString(),
        ]);

        $this->calculator->refresh($feeInvoice);

        return redirect()->route('fee-invoices.show', $feeInvoice)
            ->with('status', __('Invoice issued.'));
    }

    public function cancel(Request $request, FeeInvoice $feeInvoice): RedirectResponse
    {
        $this->authorize('cancel', $feeInvoice);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $feeInvoice->update([
            'status' => FeeInvoice::STATUS_CANCELLED,
            'notes' => trim((string) $feeInvoice->notes."\n[".now()->toDateString().'] '.__('Cancelled: ').$data['reason']),
        ]);

        $this->logger->log(
            'fee_invoice_cancelled',
            __('Invoice :n cancelled', ['n' => $feeInvoice->invoice_number]),
            ['reason' => $data['reason']],
            (int) $feeInvoice->tenant_id,
            FeeInvoice::class,
            (int) $feeInvoice->id,
        );

        return redirect()->route('fee-invoices.show', $feeInvoice)
            ->with('status', __('Invoice cancelled.'));
    }

    public function pdf(FeeInvoice $feeInvoice): Response
    {
        $this->authorize('view', $feeInvoice);

        $feeInvoice->load(['student.schoolClass', 'items', 'payments']);
        $school = School::query()->where('tenant_id', $feeInvoice->tenant_id)->first();

        $pdf = Pdf::loadView('fee-invoices.pdf', [
            'invoice' => $feeInvoice,
            'school' => $school,
        ])->setPaper('a4');

        return $pdf->download('invoice-'.$feeInvoice->invoice_number.'.pdf');
    }

    /**
     * INV-YYYYMM-XXXXX where XXXXX is a short random suffix. Cheap to keep
     * unique per tenant via the indexed (tenant_id, invoice_number) pair.
     */
    private function generateInvoiceNumber(int $tenantId): string
    {
        do {
            $candidate = 'INV-'.now()->format('Ym').'-'.strtoupper(Str::random(5));
        } while (FeeInvoice::query()
            ->where('tenant_id', $tenantId)
            ->where('invoice_number', $candidate)
            ->exists());

        return $candidate;
    }
}
