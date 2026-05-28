<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreSchoolClassRequest;
use App\Http\Requests\Settings\UpdateSchoolClassRequest;
use App\Models\SchoolClass;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user && $user->role === UserRole::Teacher->value) {
            $classes = $user->assignedClasses()->orderBy('name')->orderBy('section')->get();

            return view('classes.index', [
                'classes' => $classes,
                'readOnly' => true,
            ]);
        }

        $this->authorize('settings.manage');

        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();

        return view('classes.index', [
            'classes' => $classes,
            'readOnly' => false,
        ]);
    }

    public function store(StoreSchoolClassRequest $request): RedirectResponse
    {
        SchoolClass::query()->create($request->validated());

        return redirect()->route('classes.index')->with('status', __('Class added.'));
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update($request->validated());

        return redirect()->route('classes.index')->with('status', __('Class updated.'));
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        $this->authorize('settings.manage');

        try {
            $schoolClass->delete();
        } catch (QueryException) {
            return redirect()->route('classes.index')->with('error', __('This class cannot be deleted while students or other records reference it.'));
        }

        return redirect()->route('classes.index')->with('status', __('Class removed.'));
    }
}
