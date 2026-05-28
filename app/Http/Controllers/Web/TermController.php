<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreTermRequest;
use App\Http\Requests\Settings\UpdateTermRequest;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Services\AcademicContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TermController extends Controller
{
    public function __construct(private readonly AcademicContext $academic) {}

    public function index(Request $request): View
    {
        $this->authorize('settings.manage');

        $currentYear = $this->academic->currentYear();
        $selectedYearId = $request->integer('academic_year_id') ?: $currentYear?->id;

        $terms = Term::query()
            ->with('academicYear')
            ->when($selectedYearId, fn ($q) => $q->where('academic_year_id', $selectedYearId))
            ->orderBy('term_order')
            ->orderBy('name')
            ->get();

        $years = AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get();

        return view('terms.index', [
            'terms' => $terms,
            'years' => $years,
            'currentYear' => $currentYear,
            'currentTerm' => $this->academic->currentTerm(),
            'selectedYearId' => $selectedYearId,
        ]);
    }

    public function store(StoreTermRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $makeCurrent = (bool) ($data['is_current'] ?? false);
        unset($data['is_current']);
        $data['status'] = $data['status'] ?? Term::STATUS_ACTIVE;

        $term = Term::query()->create($data);

        if ($makeCurrent) {
            $this->flipToCurrent($term);
        }

        return redirect()->route('terms.index')->with('status', __('Term added.'));
    }

    public function update(UpdateTermRequest $request, Term $term): RedirectResponse
    {
        $data = $request->validated();
        $makeCurrent = (bool) ($data['is_current'] ?? false);
        unset($data['is_current']);
        $data['status'] = $data['status'] ?? $term->status;

        $term->update($data);

        if ($makeCurrent && ! $term->is_current) {
            $this->flipToCurrent($term);
        }

        return redirect()->route('terms.index')->with('status', __('Term updated.'));
    }

    public function destroy(Term $term): RedirectResponse
    {
        $this->authorize('settings.manage');

        if ($term->is_current) {
            return redirect()->route('terms.index')
                ->with('error', __('You cannot remove the current term. Switch to another term first.'));
        }

        try {
            $term->delete();
        } catch (QueryException) {
            return redirect()->route('terms.index')->with('error', __('This term cannot be deleted while fees or other records reference it.'));
        }

        return redirect()->route('terms.index')->with('status', __('Term removed.'));
    }

    public function setCurrent(Term $term): RedirectResponse
    {
        $this->authorize('settings.manage');

        $year = $term->academicYear;
        if ($year === null) {
            return redirect()->route('terms.index')
                ->with('error', __('Assign this term to an academic year before marking it current.'));
        }

        if (! $year->is_current) {
            return redirect()->route('terms.index')
                ->with('error', __('The current term must belong to the current academic year. Mark the academic year as current first.'));
        }

        $this->flipToCurrent($term);

        return redirect()->route('terms.index')->with('status', __('Current term updated.'));
    }

    private function flipToCurrent(Term $term): void
    {
        $year = $term->loadMissing('academicYear')->academicYear;

        if ($year && ! $year->is_current) {
            // Keep the invariant: current term must live inside the current
            // academic year. Refuse to flip silently here so the UI shows
            // the user why their click was ignored.
            session()->flash('error', __('The current term must belong to the current academic year.'));

            return;
        }

        $this->academic->markTermCurrent($term);
    }
}
