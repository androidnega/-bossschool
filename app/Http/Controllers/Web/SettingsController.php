<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSchoolRequest;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $this->authorize('settings.manage');

        $tenant = app('currentTenant');
        $school = School::query()->firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'name' => $tenant->name,
                'logo' => null,
                'address' => null,
                'phone' => null,
                'email' => null,
                'academic_year' => null,
            ]
        );

        return view('settings.index', compact('school'));
    }

    public function updateSchool(UpdateSchoolRequest $request): RedirectResponse
    {
        $tenant = app('currentTenant');
        $school = School::query()->firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'name' => $tenant->name,
                'logo' => null,
                'address' => null,
                'phone' => null,
                'email' => null,
                'academic_year' => null,
            ]
        );

        $data = $request->safe()->except('logo');

        if ($request->hasFile('logo')) {
            if ($school->logo) {
                Storage::disk('public')->delete($school->logo);
            }
            $data['logo'] = $request->file('logo')->store('school-logos', 'public');
        }

        $school->update($data);

        return redirect()->route('settings.index')->with('status', __('School profile updated.'));
    }
}
