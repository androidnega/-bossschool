@extends('layouts.app')

@section('title', __('Attendance'))

@section('header-title', __('Attendance'))

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-primary">{{ __('Attendance') }}</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('Daily attendance for each class. Use the mark button to record present, absent, late, or excused.') }}
            </p>
        </div>
    </div>

    @if ($currentTerm)
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">
            <strong>{{ __('Current academic year') }}:</strong> {{ $currentYear?->name ?? '—' }}
            · <strong>{{ __('Current term') }}:</strong> {{ $currentTerm->name }}
        </div>
    @else
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50/60 px-4 py-3 text-sm text-amber-900">
            {{ __('Set a current academic year and term before marking attendance.') }}
        </div>
    @endif

    @if (session('status'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
    @endif

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-page p-5">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-secondary">{{ __('Mark attendance') }}</h2>
            <form method="GET" action="{{ route('attendance.create', ['schoolClass' => 'placeholder']) }}" id="mark-form" class="mt-3 space-y-3">
                <div>
                    <label for="mark_class_id" class="block text-sm font-medium text-gray-700">{{ __('Class') }}</label>
                    <select id="mark_class_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" @selected((int) $classId === (int) $c->id)>{{ $c->name }}@if($c->section) — {{ $c->section }}@endif</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="mark_date" class="block text-sm font-medium text-gray-700">{{ __('Date') }}</label>
                    <input id="mark_date" type="date" value="{{ $date ?? now()->toDateString() }}" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                </div>
                <button type="button" id="mark-go" class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Open mark sheet') }}</button>
            </form>
        </div>
        <form method="GET" action="{{ route('attendance.index') }}" class="rounded-lg border border-gray-200 bg-page p-5 md:col-span-2">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-secondary">{{ __('Filter records') }}</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-3">
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700">{{ __('Class') }}</label>
                    <select name="class_id" id="class_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" @selected((int) $classId === (int) $c->id)>{{ $c->name }}@if($c->section) — {{ $c->section }}@endif</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="term_id" class="block text-sm font-medium text-gray-700">{{ __('Term') }}</label>
                    <select name="term_id" id="term_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                        <option value="">{{ __('Any') }}</option>
                        @foreach ($terms as $t)
                            <option value="{{ $t->id }}" @selected((int) $termId === (int) $t->id)>{{ $t->name }} · {{ $t->academicYear?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">{{ __('Date') }}</label>
                    <input id="date" name="date" type="date" value="{{ $date }}" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="rounded-md border border-secondary/60 bg-page px-4 py-2 text-sm font-medium text-secondary hover:bg-page-soft">{{ __('Apply filter') }}</button>
            </div>
        </form>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-page">
        @if ($entries->isEmpty())
            <div class="p-6">
                <x-empty-state :title="__('No attendance records')" :message="__('Use the mark sheet on the left to record attendance for this class.')" />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-page-soft">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Date') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Student') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Status') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Remarks') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Marked by') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-page">
                        @foreach ($entries as $row)
                            <tr>
                                <td class="px-4 py-3 text-gray-700 tabular-nums">{{ $row->date->toDateString() }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $row->student?->name }}</td>
                                <td class="px-4 py-3 capitalize text-gray-700">{{ $row->status }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $row->remarks ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $row->marker?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script>
        document.getElementById('mark-go')?.addEventListener('click', function () {
            const classId = document.getElementById('mark_class_id').value;
            const date = document.getElementById('mark_date').value;
            if (!classId) return;
            const base = "{{ url('/attendance') }}";
            window.location.href = `${base}/${classId}/mark?date=${encodeURIComponent(date)}`;
        });
    </script>
@endsection
