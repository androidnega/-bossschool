<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fee\StoreFeeRequest;
use App\Http\Requests\Fee\UpdateFeeRequest;
use App\Models\Fee;
use App\Models\SchoolClass;
use App\Models\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Fee::class, 'fee', ['except' => ['show']]);
    }

    public function index(): View
    {
        $fees = Fee::query()
            ->with(['schoolClass', 'term'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('fees.index', compact('fees'));
    }

    public function create(): View
    {
        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();
        $terms = Term::query()->orderBy('name')->get();

        return view('fees.create', compact('classes', 'terms'));
    }

    public function store(StoreFeeRequest $request): RedirectResponse
    {
        Fee::query()->create($request->validated());

        return redirect()->route('fees.index')->with('status', __('Fee saved.'));
    }

    public function edit(Fee $fee): View
    {
        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();
        $terms = Term::query()->orderBy('name')->get();

        return view('fees.edit', compact('fee', 'classes', 'terms'));
    }

    public function update(UpdateFeeRequest $request, Fee $fee): RedirectResponse
    {
        $fee->update($request->validated());

        return redirect()->route('fees.index')->with('status', __('Fee updated.'));
    }

    public function destroy(Fee $fee): RedirectResponse
    {
        $fee->delete();

        return redirect()->route('fees.index')->with('status', __('Fee removed.'));
    }
}
