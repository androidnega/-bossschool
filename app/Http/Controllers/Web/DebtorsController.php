<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DebtorsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Fee::class);

        $classId = $request->query('class_id');
        $query = Student::query()->with('schoolClass');

        if ($classId !== null && $classId !== '') {
            $query->where('class_id', (int) $classId);
        }

        $students = $query->orderBy('name')->paginate(25)->withQueryString();

        $expectedByClass = Fee::query()
            ->selectRaw('class_id, SUM(amount) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $paidByStudent = Payment::query()
            ->selectRaw('student_id, SUM(amount) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();

        return view('debtors.index', [
            'students' => $students,
            'expectedByClass' => $expectedByClass,
            'paidByStudent' => $paidByStudent,
            'classes' => $classes,
            'filters' => [
                'class_id' => $classId !== null && $classId !== '' ? (string) $classId : '',
            ],
        ]);
    }
}
