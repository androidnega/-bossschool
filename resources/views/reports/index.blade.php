@extends('layouts.app')

@section('title', __('Reports'))

@section('header-title', __('Reports'))

@section('content')
    @include('reports._subnav')

    <div>
        <h1 class="text-2xl font-semibold text-primary">{{ __('Reports') }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ __('Choose a report category.') }}</p>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @can('reports.finance')
            <a href="{{ route('reports.finance') }}" class="rounded-2xl border border-stone-200/80 bg-card-mist p-6 ring-1 ring-stone-200/40 transition">
                <span class="flex size-10 items-center justify-center rounded-lg bg-white/70 text-stone-700 ring-1 ring-stone-200/60">
                    <i class="fa-solid fa-coins" aria-hidden="true"></i>
                </span>
                <h2 class="mt-4 text-lg font-semibold text-stone-900">{{ __('Finance') }}</h2>
                <p class="mt-2 text-sm text-stone-600">{{ __('Revenue, outstanding fees, and term allocation.') }}</p>
            </a>
        @endcan
        @can('reports.students')
            <a href="{{ route('reports.students') }}" class="rounded-2xl border border-stone-200/80 bg-card-sage p-6 ring-1 ring-stone-200/40 transition">
                <span class="flex size-10 items-center justify-center rounded-lg bg-white/70 text-stone-700 ring-1 ring-stone-200/60">
                    <i class="fa-solid fa-users" aria-hidden="true"></i>
                </span>
                <h2 class="mt-4 text-lg font-semibold text-stone-900">{{ __('Students') }}</h2>
                <p class="mt-2 text-sm text-stone-600">{{ __('Enrolment, classes, and gender breakdown.') }}</p>
            </a>
        @endcan
        @can('reports.academic')
            <a href="{{ route('reports.academic') }}" class="rounded-2xl border border-stone-200/80 bg-card-sand p-6 ring-1 ring-stone-200/40 transition">
                <span class="flex size-10 items-center justify-center rounded-lg bg-white/70 text-stone-700 ring-1 ring-stone-200/60">
                    <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                </span>
                <h2 class="mt-4 text-lg font-semibold text-stone-900">{{ __('Academic') }}</h2>
                <p class="mt-2 text-sm text-stone-600">{{ __('Subject averages, top students, weak subjects.') }}</p>
            </a>
        @endcan
    </div>
@endsection
