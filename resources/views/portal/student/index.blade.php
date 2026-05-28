@extends('layouts.app')

@section('title', __('Student home'))

@section('header-title', __('My dashboard'))

@section('content')
    <div class="flex flex-col gap-3 border-b border-sky-200/60 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-sky-950">{{ __('Hello, :name', ['name' => $student->name]) }}</h1>
            <p class="mt-1 flex items-center gap-2 text-sm text-sky-900/80"><i class="fa-solid fa-users-between-lines text-sky-600" aria-hidden="true"></i>{{ $student->schoolClass?->name }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('portal.student.report-card') }}" class="inline-flex items-center gap-2 rounded-lg bg-sky-700 px-3 py-2 text-sm font-medium text-white hover:bg-sky-800"><i class="fa-solid fa-scroll" aria-hidden="true"></i>{{ __('Results') }}</a>
            <span class="inline-flex items-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-medium text-sky-900"><i class="fa-solid fa-lock" aria-hidden="true"></i>{{ __('Read-only fee view') }}</span>
        </div>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <x-dash-card icon="fa-solid fa-scale-unbalanced" :label="__('Fee balance')" :value="cedis($feeBalance)" variant="debtors" :hint="__('Office handles payments.')" />
        <x-dash-card icon="fa-solid fa-book" :label="__('Subjects')" :value="number_format($subjects->count())" variant="attendance" />
        <x-dash-card icon="fa-solid fa-chart-line" :label="__('Result rows')" :value="number_format($student->results->count())" variant="results" />
        <x-dash-card icon="fa-solid fa-calendar-check" :label="__('Attendance rows')" :value="number_format($attendance->count())" variant="attendance" />
    </div>

    @if ($latestResults->isNotEmpty())
        <div class="mt-10">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-chart-line text-emerald-700" aria-hidden="true"></i>{{ __('Latest results') }}</h2>
            <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-2">{{ __('Subject') }}</th>
                            <th class="px-4 py-2">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($latestResults as $r)
                            <tr>
                                <td class="px-4 py-2">{{ $r->subject?->name }}</td>
                                <td class="px-4 py-2 font-semibold">{{ ($r->class_test ?? 0) + ($r->midterm ?? 0) + ($r->exam ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="mt-10 grid gap-6 lg:grid-cols-2">
        <div>
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-book-open text-indigo-700" aria-hidden="true"></i>{{ __('Subjects') }}</h2>
            <ul class="mt-3 flex flex-wrap gap-2">
                @forelse ($subjects as $sub)
                    <li class="dash-card-results rounded-lg border border-black/[0.06] px-3 py-2 text-sm font-medium text-slate-800">{{ $sub->name }}</li>
                @empty
                    <li class="text-sm text-slate-600">{{ __('No subjects yet.') }}</li>
                @endforelse
            </ul>
        </div>
        <div>
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-calendar-week text-sky-700" aria-hidden="true"></i>{{ __('Attendance (recent)') }}</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($attendance as $a)
                    <span class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700"><i class="fa-solid fa-calendar-day text-slate-400" aria-hidden="true"></i>{{ $a->date?->toDateString() }}: {{ $a->status }}</span>
                @endforeach
            </div>
        </div>
    </div>

    @if ($messages->isNotEmpty())
        <div class="mt-10">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-bullhorn text-amber-600" aria-hidden="true"></i>{{ __('Notices for you') }}</h2>
            <ul class="mt-3 space-y-3">
                @foreach ($messages as $m)
                    <li class="dash-card-messages rounded-xl border border-black/[0.06] p-4 text-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">{{ __('Notice') }}</p>
                        @if ($m->title)
                            <p class="mt-1 font-medium text-slate-900">{{ $m->title }}</p>
                        @endif
                        <p class="text-xs text-slate-500">{{ $m->sent_at?->diffForHumans() }} · {{ $m->sender?->name ?? __('School') }} · {{ $m->audienceDisplay() }}</p>
                        <p class="mt-2 text-slate-800">{{ $m->content }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
