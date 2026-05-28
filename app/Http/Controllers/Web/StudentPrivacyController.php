<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Models\Result;
use App\Models\Student;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentPrivacyController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}

    /**
     * Export everything we know about a student (name, parent info, attendance
     * dates, results, invoice headers, payment history). Sensitive provider
     * raw payloads and other tenants' rows are NEVER included.
     */
    public function export(Request $request, Student $student): StreamedResponse
    {
        $this->authorizeAdmin();
        $this->ensureSameTenant($student);

        $payload = [
            'student' => $student->toArray(),
            'class' => $student->schoolClass?->toArray(),
            'attendance' => Attendance::query()->where('student_id', $student->id)->get()->map->toArray()->all(),
            'results' => Result::query()->where('student_id', $student->id)->get()->map->toArray()->all(),
            'fee_invoices' => FeeInvoice::query()->where('student_id', $student->id)->get()->map->toArray()->all(),
            'payments' => Payment::query()->where('student_id', $student->id)->get()
                ->map(fn ($p) => array_diff_key($p->toArray(), ['provider_payload' => true]))
                ->all(),
            'exported_at' => now()->toIso8601String(),
        ];

        $this->logger->log(
            'student_personal_data_exported',
            sprintf('Personal data export for student #%d', $student->id),
            ['student_id' => $student->id],
            (int) $student->tenant_id,
            Student::class,
            (int) $student->id
        );

        $filename = 'student-'.$student->id.'-'.now()->format('Ymd-His').'.json';

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Replace personal identifiers on the student record with neutral
     * placeholders while leaving every financial/academic row intact (rows
     * are linked by student_id only). This is the GDPR/PDPA-friendly "right
     * to be forgotten" path without destroying audit trails.
     */
    public function anonymize(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeAdmin();
        $this->ensureSameTenant($student);

        $request->validate([
            'confirm' => ['required', 'string', 'in:ANONYMIZE'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $before = $student->only(['name', 'parent_name', 'parent_phone', 'address']);

        $student->forceFill([
            'name' => 'Anonymized student #'.$student->id,
            'parent_name' => null,
            'parent_phone' => null,
            'address' => null,
            'gender' => null,
            'dob' => null,
            'status' => $student->status === 'active' ? 'inactive' : $student->status,
        ])->save();

        $this->logger->log(
            'student_anonymized',
            sprintf('Student #%d anonymized', $student->id),
            ['reason' => (string) $request->input('reason'), 'student_id' => $student->id, 'before_redacted' => array_keys($before)],
            (int) $student->tenant_id,
            Student::class,
            (int) $student->id
        );

        return redirect()->route('students.index')->with('status', __('Student personal data anonymized. Academic and financial history kept.'));
    }

    private function authorizeAdmin(): void
    {
        $u = auth()->user();
        if (! $u || ! in_array($u->role, [UserRole::Admin->value, UserRole::Proprietor->value], true)) {
            abort(403);
        }
    }

    private function ensureSameTenant(Student $student): void
    {
        if ((int) $student->tenant_id !== (int) auth()->user()->tenant_id) {
            abort(403);
        }
    }
}
