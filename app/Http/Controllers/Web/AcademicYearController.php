<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreAcademicYearRequest;
use App\Http\Requests\Settings\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\AcademicContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function __construct(private readonly AcademicContext $academic)
    {
        $this->authorizeResource(AcademicYear::class, 'academic_year');
    }

    public function index(): View
    {
        $years = AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get();

        return view('academic-years.index', [
            'years' => $years,
            'currentYear' => $this->academic->currentYear(),
        ]);
    }

    public function create(): View
    {
        return view('academic-years.create');
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by_user_id'] = (int) $request->user()->id;
        $data['status'] = $data['status'] ?? AcademicYear::STATUS_ACTIVE;
        $makeCurrent = (bool) ($data['is_current'] ?? false);
        unset($data['is_current']);

        $year = AcademicYear::query()->create($data);

        if ($makeCurrent) {
            $this->academic->markYearCurrent($year);
        }

        return redirect()->route('academic-years.index')->with('status', __('Academic year created.'));
    }

    public function edit(AcademicYear $academicYear): View
    {
        return view('academic-years.edit', ['year' => $academicYear]);
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $data = $request->validated();
        $makeCurrent = (bool) ($data['is_current'] ?? false);
        unset($data['is_current']);
        $data['status'] = $data['status'] ?? $academicYear->status;

        $academicYear->update($data);

        if ($makeCurrent && ! $academicYear->is_current) {
            $this->academic->markYearCurrent($academicYear);
        }

        return redirect()->route('academic-years.index')->with('status', __('Academic year updated.'));
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->is_current) {
            return redirect()->route('academic-years.index')
                ->with('error', __('You cannot remove the current academic year. Switch to another year first.'));
        }

        $academicYear->delete();

        return redirect()->route('academic-years.index')->with('status', __('Academic year removed.'));
    }

    public function setCurrent(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('setCurrent', $academicYear);

        $this->academic->markYearCurrent($academicYear);

        return redirect()->route('academic-years.index')->with('status', __('Current academic year updated.'));
    }
}
