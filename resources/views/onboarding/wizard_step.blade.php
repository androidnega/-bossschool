@extends('layouts.app')

@section('title', __('Onboarding step :n', ['n' => $step]))
@section('header-title', __('Onboarding — :label', ['label' => $meta['label']]))

@section('content')
    <div class="mb-4 text-sm">
        <a href="{{ route('onboarding.wizard.index') }}" class="text-primary hover:underline">{{ __('← Back to wizard') }}</a>
    </div>

    @php
        $detected = (bool) ($auto[$meta['key']] ?? false);
        $next = min($step + 1, count(\App\Services\OnboardingWizardService::STEPS));
        $links = [
            'school_profile' => ['settings.index', __('Open school profile')],
            'academic_year' => ['academic-years.index', __('Open academic years')],
            'terms' => ['terms.index', __('Open terms')],
            'classes' => ['classes.index', __('Open classes')],
            'subjects' => ['subjects.index', __('Open subjects')],
            'staff' => ['staff.index', __('Open staff')],
            'students' => ['students.index', __('Open students')],
            'fees' => ['fees.index', __('Open fees')],
            'first_backup' => ['backups.index', __('Open backups')],
        ];
    @endphp

    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm">
        <p class="mb-3 text-slate-600">{{ __('Status:') }}
            @if($detected)
                <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">{{ __('Detected — data exists') }}</span>
            @else
                <span class="rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-700">{{ __('Not yet detected') }}</span>
            @endif
        </p>
        @if(isset($links[$meta['key']]))
            <p class="mb-3">
                <a href="{{ route($links[$meta['key']][0]) }}" class="rounded-md bg-primary px-3 py-1.5 text-xs text-white">
                    {{ $links[$meta['key']][1] }}
                </a>
            </p>
        @endif

        <form method="POST" action="{{ route('onboarding.wizard.mark', ['step' => $step]) }}" class="flex items-center gap-2">
            @csrf
            <button type="submit" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs">
                {{ __('Mark step as done and continue') }}
            </button>
            @if($step < count(\App\Services\OnboardingWizardService::STEPS))
                <a href="{{ route('onboarding.wizard.show', ['step' => $next]) }}" class="text-xs text-primary hover:underline">{{ __('Skip to next step') }}</a>
            @endif
        </form>
    </div>
@endsection
