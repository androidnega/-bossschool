@extends('layouts.app')

@section('title', __('Teacher dashboard'))

@section('header-title', __('Teacher dashboard'))

@section('content')
    <div class="flex flex-col gap-3 border-b border-indigo-200/60 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-indigo-950">{{ __('My teaching load') }}</h1>
            <p class="mt-1 text-sm text-indigo-900/75">{{ __('Only assigned classes and subjects.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('results.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-800"><i class="fa-solid fa-chart-line" aria-hidden="true"></i>{{ __('Enter results') }}</a>
            <a href="{{ route('classes.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>{{ __('My classes') }}</a>
            <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-scroll" aria-hidden="true"></i>{{ __('Report cards') }}</a>
            @can('viewAny', \App\Models\Message::class)
                <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i>{{ __('Notices') }}</a>
            @endcan
        </div>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <x-dash-card icon="fa-solid fa-users-between-lines" :label="__('Assigned classes')" :value="number_format($classes->count())" variant="attendance" />
        <x-dash-card icon="fa-solid fa-book" :label="__('Assigned subjects')" :value="number_format($subjects->count())" variant="results" />
        <x-dash-card icon="fa-solid fa-user-graduate" :label="__('Students in reach')" :value="number_format($studentsInClasses)" variant="students" />
        <x-dash-card icon="fa-solid fa-calendar-check" :label="__('Attendance today')" :value="number_format($attendanceToday)" variant="attendance" :hint="__('Absent: :n', ['n' => number_format($absentToday)])" />
    </div>

    <div class="mt-3 grid gap-3 sm:grid-cols-2">
        <x-dash-card icon="fa-solid fa-pen-to-square" :label="__('Results entered')" :value="number_format($resultsEntered)" variant="results" />
        <x-dash-card icon="fa-solid fa-hourglass-half" :label="__('Results pending')" :value="number_format($resultsPending)" variant="debtors" />
    </div>

    <div class="mt-4 grid gap-3 lg:grid-cols-2">
        <x-dash-card icon="fa-solid fa-chart-simple" :label="__('Avg. total score (entered)')" :value="number_format($avgScore ?? 0, 1)" variant="neutral" :hint="__('Assigned subjects only.')" />
        <div class="dash-card-messages rounded-xl border border-black/[0.06] p-4 sm:p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Assigned subjects') }}</p>
            <ul class="mt-2 flex flex-wrap gap-2 text-sm text-slate-800">
                @foreach ($subjects as $sub)
                    <li class="rounded-lg bg-white/80 px-2 py-1 ring-1 ring-black/[0.06]">{{ $sub->name }} <span class="text-slate-500">({{ $sub->schoolClass?->name }})</span></li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="mt-10">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-bullhorn text-indigo-700" aria-hidden="true"></i>{{ __('Notices for you & your classes') }}</h2>
        <ul class="mt-3 space-y-2">
            @forelse ($recentNotices as $m)
                <li class="dash-card-messages rounded-xl border border-black/[0.06] p-3 text-sm text-slate-800">
                    <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">{{ __('Notice') }}</p>
                    @if ($m->title)
                        <p class="mt-1 font-medium text-slate-900">{{ $m->title }}</p>
                    @endif
                    <p class="text-xs text-slate-500">{{ $m->sent_at?->diffForHumans() }} · {{ $m->sender?->name ?? __('System') }} · {{ $m->notice_kind ?? $m->channel ?? '—' }}</p>
                    <p class="mt-1 text-xs text-slate-600">{{ $m->audienceDisplay() }}</p>
                    <p class="mt-1">{{ \Illuminate\Support\Str::limit($m->content, 120) }}</p>
                </li>
            @empty
                <li class="text-sm text-slate-600">{{ __('No notices yet.') }}</li>
            @endforelse
        </ul>
    </div>

    <div class="mt-10">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-scroll text-indigo-700" aria-hidden="true"></i>{{ __('Recent report card access') }}</h2>
        <p class="mt-1 text-sm text-slate-600">{{ __('Students in your classes (open profile or report card from Students).') }}</p>
        <ul class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
            @foreach ($recentReportStudents as $s)
                <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                    <span class="font-medium text-slate-900">{{ $s->name }}</span>
                    <span class="text-slate-600">{{ $s->schoolClass?->name }}</span>
                    <a href="{{ route('students.report-card', $s) }}" class="text-indigo-700 hover:underline"><i class="fa-solid fa-file-lines me-1" aria-hidden="true"></i>{{ __('Report card') }}</a>
                </li>
            @endforeach
        </ul>
    </div>

@endsection
