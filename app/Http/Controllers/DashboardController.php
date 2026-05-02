<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $studentCount = Student::query()->count();

        $feesTotal = (float) Fee::query()->sum('amount');
        $collected = (float) Payment::query()->sum('amount');
        $outstanding = max(0.0, $feesTotal - $collected);

        return view('dashboard', [
            'studentCount' => $studentCount,
            'feesCollected' => $collected,
            'outstandingFees' => $outstanding,
        ]);
    }
}
