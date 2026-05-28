<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Services\StudentFinanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class FeeStatementController extends Controller
{
    public function __construct(private readonly StudentFinanceService $finance) {}

    /** Admin / Proprietor / Accountant view of any student in their tenant. */
    public function show(Student $student): View
    {
        $this->authorize('view', $student);

        return view('finance.statement', $this->payload($student));
    }

    public function showPdf(Student $student): Response
    {
        $this->authorize('view', $student);

        return $this->renderPdf($student);
    }

    /** Parent portal: scoped to a linked child. */
    public function parent(Request $request, Student $student): View
    {
        $user = $request->user();
        abort_unless($user->isGuardianOf($student), 403);

        return view('finance.statement', $this->payload($student));
    }

    public function parentPdf(Request $request, Student $student): Response
    {
        $user = $request->user();
        abort_unless($user->isGuardianOf($student), 403);

        return $this->renderPdf($student);
    }

    /** Student portal: only their own statement. */
    public function student(Request $request): View
    {
        $user = $request->user();
        $student = $user->student;
        abort_unless($student, 404);

        return view('finance.statement', $this->payload($student));
    }

    public function studentPdf(Request $request): Response
    {
        $user = $request->user();
        $student = $user->student;
        abort_unless($student, 404);

        return $this->renderPdf($student);
    }

    private function payload(Student $student): array
    {
        $data = $this->finance->statement($student);
        $data['school'] = School::query()->where('tenant_id', $student->tenant_id)->first();

        return $data;
    }

    private function renderPdf(Student $student): Response
    {
        $data = $this->payload($student);
        $pdf = Pdf::loadView('finance.statement-pdf', $data)->setPaper('a4');

        return $pdf->download(Str::slug($student->name).'-statement.pdf');
    }
}
