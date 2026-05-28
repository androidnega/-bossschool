<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EndOfTerm\ReopenEndOfTermRunRequest;
use App\Http\Requests\EndOfTerm\StoreEndOfTermRunRequest;
use App\Http\Requests\EndOfTerm\UpdateEndOfTermRunRequest;
use App\Models\AcademicYear;
use App\Models\EndOfTermRun;
use App\Models\Term;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Walks a school through "closing" a term — attendance, results, fees, etc.
 *
 * The workflow is a single end_of_term_runs row whose checklist is a JSON
 * map of step => bool. Only Admin / Proprietor may move the row through
 * close + reopen; the controller and policy both enforce that.
 *
 * Closing a term never deletes data. Re-opening writes an audit log entry
 * with a reason, and the result policy reacts to status='closed' by
 * blocking teacher edits for the affected term.
 */
class EndOfTermController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', EndOfTermRun::class);

        $runs = EndOfTermRun::query()
            ->with(['term.academicYear', 'academicYear', 'initiator', 'closer'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('end-of-term.index', [
            'runs' => $runs,
            'terms' => Term::query()->with('academicYear')->orderByDesc('term_order')->orderBy('name')->get(),
            'years' => AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get(),
        ]);
    }

    public function store(StoreEndOfTermRunRequest $request): RedirectResponse
    {
        $tenantId = (int) $request->user()->tenant_id;
        $yearId = (int) $request->validated('academic_year_id');
        $termId = (int) $request->validated('term_id');

        $existing = EndOfTermRun::query()
            ->where('tenant_id', $tenantId)
            ->where('term_id', $termId)
            ->first();

        if ($existing) {
            return redirect()->route('end-of-term.show', $existing)
                ->with('status', __('Workflow already started for this term.'));
        }

        $checklist = collect(EndOfTermRun::DEFAULT_CHECKLIST)
            ->mapWithKeys(fn ($k) => [$k => false])
            ->toArray();

        $run = EndOfTermRun::query()->create([
            'tenant_id' => $tenantId,
            'academic_year_id' => $yearId,
            'term_id' => $termId,
            'initiated_by_user_id' => $request->user()->id,
            'status' => EndOfTermRun::STATUS_DRAFT,
            'checklist' => $checklist,
            'notes' => $request->validated('notes'),
        ]);

        return redirect()->route('end-of-term.show', $run);
    }

    public function show(Request $request, EndOfTermRun $endOfTermRun): View
    {
        $this->authorize('view', $endOfTermRun);

        $endOfTermRun->loadMissing(['term.academicYear', 'academicYear', 'initiator', 'closer', 'reopener']);

        // Term 3 (term_order = 3) is the only term where the promotion step
        // makes sense. The view branches on this flag so KG / Term 1 / Term 2
        // runs don't show a misleading "Promote students" checkbox.
        $isFinalTerm = (int) ($endOfTermRun->term?->term_order ?? 0) === 3;

        return view('end-of-term.show', [
            'run' => $endOfTermRun,
            'checklistLabels' => $this->checklistLabels(),
            'isFinalTerm' => $isFinalTerm,
        ]);
    }

    public function update(UpdateEndOfTermRunRequest $request, EndOfTermRun $endOfTermRun, ActivityLogger $logger): RedirectResponse
    {
        $checklist = collect(EndOfTermRun::DEFAULT_CHECKLIST)
            ->mapWithKeys(fn ($k) => [$k => (bool) (data_get($request->validated('checklist', []), $k) ?? false)])
            ->toArray();

        $action = $request->validated('action') ?? 'save';

        $endOfTermRun->checklist = $checklist;
        if ($request->filled('notes')) {
            $endOfTermRun->notes = $request->validated('notes');
        }

        if ($action === 'close') {
            $endOfTermRun->status = EndOfTermRun::STATUS_CLOSED;
            $endOfTermRun->closed_at = now();
            $endOfTermRun->closed_by_user_id = $request->user()->id;
        } else {
            // Saving the checklist without closing leaves the run in a
            // "reviewed" stage; nothing downstream relies on this distinction
            // yet but it gives finance a useful breadcrumb.
            if ($endOfTermRun->status === EndOfTermRun::STATUS_DRAFT) {
                $endOfTermRun->status = EndOfTermRun::STATUS_REVIEWED;
            }
        }

        $endOfTermRun->save();

        if ($action === 'close') {
            $logger->log(
                'term_closed',
                __('Closed term :term', ['term' => $endOfTermRun->term?->name]),
                ['term_id' => $endOfTermRun->term_id],
                (int) $endOfTermRun->tenant_id,
                EndOfTermRun::class,
                $endOfTermRun->id,
            );
        }

        return redirect()->route('end-of-term.show', $endOfTermRun);
    }

    public function reopen(ReopenEndOfTermRunRequest $request, EndOfTermRun $endOfTermRun, ActivityLogger $logger): RedirectResponse
    {
        $endOfTermRun->status = EndOfTermRun::STATUS_REOPENED;
        $endOfTermRun->reopened_at = now();
        $endOfTermRun->reopened_by_user_id = $request->user()->id;
        $endOfTermRun->reopen_reason = $request->validated('reopen_reason');
        $endOfTermRun->save();

        $logger->log(
            'term_reopened',
            __('Reopened term :term', ['term' => $endOfTermRun->term?->name]),
            [
                'term_id' => $endOfTermRun->term_id,
                'reason' => $endOfTermRun->reopen_reason,
            ],
            (int) $endOfTermRun->tenant_id,
            EndOfTermRun::class,
            $endOfTermRun->id,
        );

        return redirect()->route('end-of-term.show', $endOfTermRun)
            ->with('status', __('Term reopened.'));
    }

    /**
     * Display-only labels for the checklist keys. Held here so a future
     * config-driven workflow (e.g. tenant_settings) can override these.
     *
     * @return array<string, string>
     */
    private function checklistLabels(): array
    {
        return [
            'attendance_completed' => __('Attendance completed'),
            'results_entered' => __('Results entered'),
            'report_card_meta_completed' => __('Report-card meta completed'),
            'report_cards_generated' => __('Report cards generated'),
            'fee_balances_reviewed' => __('Fee balances reviewed'),
            'next_term_fees_entered' => __('Next-term fees entered'),
            'promotion_reviewed' => __('Promotion reviewed (Term 3 only)'),
            'data_exported' => __('Backups / exports completed'),
        ];
    }
}
