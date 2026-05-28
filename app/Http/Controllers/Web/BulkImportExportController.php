<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Term;
use App\Services\CsvImportResult;
use App\Services\CsvIo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImportExportController extends Controller
{
    /** Imports allowed and the roles that may run each. */
    private const IMPORT_PERMISSIONS = [
        'students' => ['Admin', 'Proprietor'],
        'staff' => ['Admin', 'Proprietor'],
        'fees' => ['Admin', 'Proprietor', 'Accountant'],
        'invoice_items' => ['Admin', 'Proprietor', 'Accountant'],
    ];

    /** Exports allowed and the roles that may run each. */
    private const EXPORT_PERMISSIONS = [
        'students' => ['Admin', 'Proprietor'],
        'staff' => ['Admin', 'Proprietor'],
        'invoices' => ['Admin', 'Proprietor', 'Accountant'],
        'payments' => ['Admin', 'Proprietor', 'Accountant'],
        'debtors' => ['Admin', 'Proprietor', 'Accountant'],
        'attendance' => ['Admin', 'Proprietor'],
        'results' => ['Admin', 'Proprietor'],
    ];

    public function index(Request $request): View
    {
        abort_unless($request->user()->isFinanceRole() || in_array($request->user()->role, [UserRole::Admin->value, UserRole::Proprietor->value], true), 403);

        return view('imports.index', [
            'importKinds' => array_keys(self::IMPORT_PERMISSIONS),
            'exportKinds' => array_keys(self::EXPORT_PERMISSIONS),
            'permissions' => [
                'import' => self::IMPORT_PERMISSIONS,
                'export' => self::EXPORT_PERMISSIONS,
            ],
            'result' => session('import_result'),
        ]);
    }

    public function template(string $kind): StreamedResponse
    {
        $headers = match ($kind) {
            'students' => ['name', 'gender', 'class_name', 'class_section', 'admission_number', 'parent_name', 'parent_phone', 'date_of_birth'],
            'staff' => ['name', 'role', 'phone', 'email'],
            'fees' => ['class_name', 'class_section', 'term_name', 'fee_type', 'amount'],
            'invoice_items' => ['invoice_number', 'description', 'category', 'quantity', 'unit_amount'],
            default => abort(404),
        };

        return CsvIo::download($kind.'-template.csv', $headers, fn () => yield from []);
    }

    public function import(Request $request, string $kind): RedirectResponse
    {
        $user = $request->user();
        $allowedRoles = self::IMPORT_PERMISSIONS[$kind] ?? null;
        abort_unless($allowedRoles, 404);
        abort_unless(in_array($user->role, $allowedRoles, true), 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $path = $request->file('file')->getRealPath();
        $tenantId = (int) $user->tenant_id;

        $result = match ($kind) {
            'students' => $this->importStudents($path, $tenantId),
            'staff' => $this->importStaff($path, $tenantId),
            'fees' => $this->importFees($path, $tenantId),
            'invoice_items' => $this->importInvoiceItems($path, $tenantId),
        };

        return redirect()
            ->route('imports.index')
            ->with('import_result', [
                'kind' => $kind,
                'total' => $result->total,
                'imported' => $result->imported,
                'errors' => $result->errors,
            ])
            ->with('status', __(':n rows imported.', ['n' => $result->imported]));
    }

    public function export(Request $request, string $kind): StreamedResponse
    {
        $user = $request->user();
        $allowedRoles = self::EXPORT_PERMISSIONS[$kind] ?? null;
        abort_unless($allowedRoles, 404);
        abort_unless(in_array($user->role, $allowedRoles, true), 403);

        return match ($kind) {
            'students' => $this->exportStudents(),
            'staff' => $this->exportStaff(),
            'invoices' => $this->exportInvoices(),
            'payments' => $this->exportPayments(),
            'debtors' => $this->exportDebtors(),
            'attendance' => $this->exportAttendance(),
            'results' => $this->exportResults(),
        };
    }

    // --- Imports -----------------------------------------------------------

    private function importStudents(string $path, int $tenantId): CsvImportResult
    {
        $result = new CsvImportResult;
        $row = 1;

        foreach (CsvIo::read($path) as $r) {
            $row++;
            $result->total++;

            $v = Validator::make($r, [
                'name' => ['required', 'string', 'max:191'],
                'gender' => ['nullable', 'string', 'in:male,female,other'],
                'class_name' => ['required', 'string', 'max:191'],
                'class_section' => ['nullable', 'string', 'max:64'],
                'admission_number' => ['nullable', 'string', 'max:64'],
                'parent_phone' => ['nullable', 'string', 'max:32'],
            ]);

            if ($v->fails()) {
                $result->fail($row, implode('; ', $v->errors()->all()));

                continue;
            }

            $class = SchoolClass::query()
                ->where('tenant_id', $tenantId)
                ->where('name', $r['class_name'])
                ->when(! empty($r['class_section']), fn ($q) => $q->where('section', $r['class_section']))
                ->first();
            if (! $class) {
                $result->fail($row, "Class '{$r['class_name']}' not found in this tenant.");

                continue;
            }

            try {
                Student::query()->create([
                    'tenant_id' => $tenantId,
                    'class_id' => $class->id,
                    'name' => $r['name'],
                    'gender' => $r['gender'] ?: null,
                    'admission_number' => $r['admission_number'] ?: null,
                    'parent_name' => $r['parent_name'] ?? null,
                    'parent_phone' => $r['parent_phone'] ?? null,
                    'date_of_birth' => $r['date_of_birth'] ?? null,
                    'status' => 'active',
                ]);
                $result->imported++;
            } catch (\Throwable $e) {
                $result->fail($row, $e->getMessage());
            }
        }

        return $result;
    }

    private function importStaff(string $path, int $tenantId): CsvImportResult
    {
        $result = new CsvImportResult;
        $row = 1;

        foreach (CsvIo::read($path) as $r) {
            $row++;
            $result->total++;

            $v = Validator::make($r, [
                'name' => ['required', 'string', 'max:191'],
                'role' => ['nullable', 'string', 'max:64'],
                'phone' => ['nullable', 'string', 'max:32'],
                'email' => ['nullable', 'email', 'max:191'],
            ]);

            if ($v->fails()) {
                $result->fail($row, implode('; ', $v->errors()->all()));

                continue;
            }

            try {
                Staff::query()->create([
                    'tenant_id' => $tenantId,
                    'name' => $r['name'],
                    'role' => $r['role'] ?: 'staff',
                    'phone' => $r['phone'] ?: null,
                    'email' => $r['email'] ?: null,
                ]);
                $result->imported++;
            } catch (\Throwable $e) {
                $result->fail($row, $e->getMessage());
            }
        }

        return $result;
    }

    private function importFees(string $path, int $tenantId): CsvImportResult
    {
        $result = new CsvImportResult;
        $row = 1;

        foreach (CsvIo::read($path) as $r) {
            $row++;
            $result->total++;

            $v = Validator::make($r, [
                'class_name' => ['required', 'string'],
                'term_name' => ['required', 'string'],
                'fee_type' => ['required', 'string', 'max:128'],
                'amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            ]);

            if ($v->fails()) {
                $result->fail($row, implode('; ', $v->errors()->all()));

                continue;
            }

            $class = SchoolClass::query()
                ->where('tenant_id', $tenantId)
                ->where('name', $r['class_name'])
                ->when(! empty($r['class_section']), fn ($q) => $q->where('section', $r['class_section']))
                ->first();
            $term = Term::query()->where('tenant_id', $tenantId)->where('name', $r['term_name'])->first();

            if (! $class || ! $term) {
                $result->fail($row, 'Class or term not found in this tenant.');

                continue;
            }

            Fee::query()->create([
                'tenant_id' => $tenantId,
                'class_id' => $class->id,
                'term_id' => $term->id,
                'fee_type' => $r['fee_type'],
                'amount' => (float) $r['amount'],
            ]);
            $result->imported++;
        }

        return $result;
    }

    private function importInvoiceItems(string $path, int $tenantId): CsvImportResult
    {
        $result = new CsvImportResult;
        $row = 1;
        $calculator = app(\App\Services\InvoiceCalculator::class);
        $invoicesTouched = [];

        foreach (CsvIo::read($path) as $r) {
            $row++;
            $result->total++;

            $v = Validator::make($r, [
                'invoice_number' => ['required', 'string'],
                'description' => ['required', 'string', 'max:191'],
                'category' => ['nullable', 'string', 'max:64'],
                'quantity' => ['nullable', 'integer', 'min:1'],
                'unit_amount' => ['required', 'numeric', 'min:0'],
            ]);

            if ($v->fails()) {
                $result->fail($row, implode('; ', $v->errors()->all()));

                continue;
            }

            $invoice = FeeInvoice::query()
                ->where('tenant_id', $tenantId)
                ->where('invoice_number', $r['invoice_number'])
                ->first();

            if (! $invoice) {
                $result->fail($row, "Invoice '{$r['invoice_number']}' not found.");

                continue;
            }

            $invoice->items()->create([
                'tenant_id' => $tenantId,
                'description' => $r['description'],
                'category' => $r['category'] ?: null,
                'quantity' => max(1, (int) ($r['quantity'] ?? 1)),
                'unit_amount' => (float) $r['unit_amount'],
            ]);
            $invoicesTouched[$invoice->id] = $invoice;
            $result->imported++;
        }

        foreach ($invoicesTouched as $invoice) {
            $calculator->refresh($invoice);
        }

        return $result;
    }

    // --- Exports -----------------------------------------------------------

    private function exportStudents(): StreamedResponse
    {
        return CsvIo::download(
            'students-'.now()->format('Ymd-His').'.csv',
            ['name', 'gender', 'class_name', 'class_section', 'admission_number', 'parent_name', 'parent_phone', 'status'],
            function () {
                foreach (Student::query()->with('schoolClass')->orderBy('name')->cursor() as $s) {
                    yield [
                        $s->name,
                        $s->gender,
                        $s->schoolClass?->name,
                        $s->schoolClass?->section,
                        $s->admission_number,
                        $s->parent_name,
                        $s->parent_phone,
                        $s->status,
                    ];
                }
            }
        );
    }

    private function exportStaff(): StreamedResponse
    {
        return CsvIo::download(
            'staff-'.now()->format('Ymd-His').'.csv',
            ['name', 'role', 'phone', 'email'],
            function () {
                foreach (Staff::query()->orderBy('name')->cursor() as $s) {
                    yield [$s->name, $s->role, $s->phone, $s->email];
                }
            }
        );
    }

    private function exportInvoices(): StreamedResponse
    {
        return CsvIo::download(
            'invoices-'.now()->format('Ymd-His').'.csv',
            ['invoice_number', 'student_name', 'term', 'year', 'status', 'subtotal', 'discount', 'waiver', 'amount_due', 'amount_paid', 'balance', 'issued_at', 'due_date'],
            function () {
                foreach (FeeInvoice::query()->with(['student', 'term', 'academicYear'])->orderBy('id')->cursor() as $i) {
                    yield [
                        $i->invoice_number,
                        $i->student?->name,
                        $i->term?->name,
                        $i->academicYear?->name,
                        $i->status,
                        $i->subtotal,
                        $i->discount_total,
                        $i->waiver_total,
                        $i->amount_due,
                        $i->amount_paid,
                        $i->balance,
                        $i->issued_at?->toDateString(),
                        $i->due_date?->toDateString(),
                    ];
                }
            }
        );
    }

    private function exportPayments(): StreamedResponse
    {
        return CsvIo::download(
            'payments-'.now()->format('Ymd-His').'.csv',
            ['receipt_id', 'date', 'student_name', 'invoice_number', 'amount', 'channel', 'provider', 'status', 'reference'],
            function () {
                foreach (Payment::query()->with(['student', 'invoice'])->orderBy('date')->orderBy('id')->cursor() as $p) {
                    yield [
                        $p->receipt_id,
                        $p->date?->toDateString(),
                        $p->student?->name,
                        $p->invoice?->invoice_number,
                        $p->amount,
                        $p->payment_channel,
                        $p->provider,
                        $p->status,
                        $p->payment_reference ?: $p->reference,
                    ];
                }
            }
        );
    }

    private function exportDebtors(): StreamedResponse
    {
        return CsvIo::download(
            'debtors-'.now()->format('Ymd-His').'.csv',
            ['student_name', 'class', 'balance', 'arrears'],
            function () {
                $finance = app(\App\Services\StudentFinanceService::class);
                foreach (Student::query()->with('schoolClass')->orderBy('name')->cursor() as $s) {
                    $statement = $finance->statement($s);
                    if (($statement['totals']['balance'] ?? 0) <= 0 && ($statement['totals']['arrears'] ?? 0) <= 0) {
                        continue;
                    }
                    yield [
                        $s->name,
                        trim(($s->schoolClass?->name ?? '').' '.($s->schoolClass?->section ?? '')),
                        $statement['totals']['balance'],
                        $statement['totals']['arrears'],
                    ];
                }
            }
        );
    }

    private function exportAttendance(): StreamedResponse
    {
        return CsvIo::download(
            'attendance-'.now()->format('Ymd-His').'.csv',
            ['date', 'student_name', 'class', 'status'],
            function () {
                foreach (Attendance::query()->with(['student.schoolClass'])->orderByDesc('date')->cursor() as $a) {
                    yield [
                        is_string($a->date) ? $a->date : $a->date?->toDateString(),
                        $a->student?->name,
                        trim(($a->student?->schoolClass?->name ?? '').' '.($a->student?->schoolClass?->section ?? '')),
                        $a->status,
                    ];
                }
            }
        );
    }

    private function exportResults(): StreamedResponse
    {
        return CsvIo::download(
            'results-'.now()->format('Ymd-His').'.csv',
            ['student_name', 'class', 'subject', 'term', 'year', 'class_test', 'midterm', 'exam', 'total', 'grade'],
            function () {
                foreach (Result::query()->with(['student.schoolClass', 'subject', 'term', 'academicYear'])->orderByDesc('id')->cursor() as $r) {
                    yield [
                        $r->student?->name,
                        trim(($r->student?->schoolClass?->name ?? '').' '.($r->student?->schoolClass?->section ?? '')),
                        $r->subject?->name,
                        $r->term?->name,
                        $r->academicYear?->name,
                        $r->class_test,
                        $r->midterm,
                        $r->exam,
                        $r->total,
                        $r->grade,
                    ];
                }
            }
        );
    }
}
