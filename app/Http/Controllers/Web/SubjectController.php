<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Subject::class, 'subject', ['except' => ['show']]);
    }

    public function index(Request $request): View
    {
        $query = Subject::query()
            ->with('schoolClass')
            ->orderBy('name');

        $user = $request->user();
        if ($user && $user->role === UserRole::Teacher->value) {
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            $query->whereIn('id', $subjectIds);
        }

        $subjects = $query->paginate(25);

        return view('subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();

        return view('subjects.create', compact('classes'));
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        Subject::query()->create($request->validated());

        return redirect()->route('subjects.index')->with('status', __('Subject saved.'));
    }

    public function edit(Subject $subject): View
    {
        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();

        return view('subjects.edit', compact('subject', 'classes'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        return redirect()->route('subjects.index')->with('status', __('Subject updated.'));
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('subjects.index')->with('status', __('Subject removed.'));
    }
}
