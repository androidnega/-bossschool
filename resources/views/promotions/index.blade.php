@extends('layouts.app')

@section('title', __('Class promotion'))

@section('header-title', __('Promotion'))

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-primary">{{ __('Class promotion') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Move students from one class to the next at end of year. Repeating and graduating students are also recorded here. Suggestions are for guidance only — you must confirm each promotion.') }}</p>
        </div>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('promotions.index') }}" class="mt-4 grid gap-3 rounded-lg border border-gray-200 bg-page p-4 sm:grid-cols-5">
        <div>
            <label for="from_class_id" class="block text-xs font-medium uppercase text-secondary">{{ __('From class') }}</label>
            <select name="from_class_id" id="from_class_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                @foreach ($classes as $c)
                    <option value="{{ $c->id }}" @selected((int) $fromClassId === (int) $c->id)>{{ $c->name }}@if ($c->section) — {{ $c->section }}@endif</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="from_academic_year_id" class="block text-xs font-medium uppercase text-secondary">{{ __('From year') }}</label>
            <select name="from_academic_year_id" id="from_academic_year_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                @foreach ($years as $y)
                    <option value="{{ $y->id }}" @selected((int) $fromYearId === (int) $y->id)>{{ $y->name }}@if ($y->is_current) ({{ __('current') }})@endif</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="term_id" class="block text-xs font-medium uppercase text-secondary">{{ __('Preview term') }}</label>
            <select name="term_id" id="term_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                @foreach ($terms as $t)
                    <option value="{{ $t->id }}" @selected((int) $termId === (int) $t->id)>
                        {{ $t->name }} · {{ $t->academicYear?->name }}@if ($t->is_current) ({{ __('current') }})@endif
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="to_class_id" class="block text-xs font-medium uppercase text-secondary">{{ __('To class') }}</label>
            <select name="to_class_id" id="to_class_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                <option value="">{{ __('— Graduate or repeat') }}</option>
                @foreach ($classes as $c)
                    <option value="{{ $c->id }}" @selected((int) $toClassId === (int) $c->id)>{{ $c->name }}@if ($c->section) — {{ $c->section }}@endif</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="to_academic_year_id" class="block text-xs font-medium uppercase text-secondary">{{ __('To year') }}</label>
            <select name="to_academic_year_id" id="to_academic_year_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                @foreach ($years as $y)
                    <option value="{{ $y->id }}" @selected((int) $toYearId === (int) $y->id)>{{ $y->name }}@if ($y->is_current) ({{ __('current') }})@endif</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-5">
            <button type="submit" class="rounded-md border border-secondary/60 bg-page px-4 py-2 text-sm font-medium text-secondary hover:bg-page-soft">{{ __('Refresh preview') }}</button>
            <span class="ml-3 text-xs text-gray-500">{{ __('Pass mark used for suggestions: :pm', ['pm' => $passMark]) }}</span>
        </div>
    </form>

    <form method="POST" action="{{ route('promotions.store') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="from_class_id" value="{{ $fromClassId }}">
        <input type="hidden" name="from_academic_year_id" value="{{ $fromYearId }}">
        <input type="hidden" name="to_class_id" value="{{ $toClassId }}">
        <input type="hidden" name="to_academic_year_id" value="{{ $toYearId }}">

        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">{{ __('Action to apply to selected students') }}</label>
                <select name="status" id="status" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm @error('status') border-red-500 @enderror">
                    <option value="{{ \App\Models\PromotionHistory::STATUS_PROMOTED }}">{{ __('Promote to next class') }}</option>
                    <option value="{{ \App\Models\PromotionHistory::STATUS_REPEATED }}">{{ __('Repeat in same class') }}</option>
                    <option value="{{ \App\Models\PromotionHistory::STATUS_GRADUATED }}">{{ __('Graduate (final year)') }}</option>
                </select>
                @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('to_class_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('Notes (optional)') }}</label>
                <input type="text" name="notes" id="notes" maxlength="500" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-page">
            @if ($rows->isEmpty())
                <div class="p-6">
                    <x-empty-state :title="__('No students in this class')" :message="__('Pick a from-class with students to begin.')" />
                </div>
            @else
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-2 text-sm">
                    <label class="inline-flex items-center gap-2"><input type="checkbox" id="check-all" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"> {{ __('Select all') }}</label>
                    <button type="button" id="check-suggested" class="text-primary hover:underline">{{ __('Select only suggested-promote') }}</button>
                    <span class="text-gray-600">{{ trans_choice('{1} :count student|[2,*] :count students', $rows->count(), ['count' => $rows->count()]) }}</span>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-page-soft">
                        <tr>
                            <th class="px-3 py-2"></th>
                            <th class="px-3 py-2 font-medium text-gray-700">{{ __('Student') }}</th>
                            <th class="px-3 py-2 font-medium text-gray-700">{{ __('Admission no.') }}</th>
                            <th class="px-3 py-2 font-medium text-gray-700 text-right">{{ __('Total') }}</th>
                            <th class="px-3 py-2 font-medium text-gray-700 text-right">{{ __('Average') }}</th>
                            <th class="px-3 py-2 font-medium text-gray-700 text-right">{{ __('Position') }}</th>
                            <th class="px-3 py-2 font-medium text-gray-700 text-right">{{ __('Present') }}</th>
                            <th class="px-3 py-2 font-medium text-gray-700 text-right">{{ __('Absent') }}</th>
                            <th class="px-3 py-2 font-medium text-gray-700">{{ __('Suggested') }}</th>
                            <th class="px-3 py-2 font-medium text-gray-700">{{ __('Current status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-page">
                        @foreach ($rows as $r)
                            @php($s = $r['student'])
                            @php($sug = $r['suggestion'])
                            @php($pill = match($sug) {
                                \App\Models\PromotionHistory::STATUS_PROMOTED => 'bg-emerald-100 text-emerald-800',
                                \App\Models\PromotionHistory::STATUS_REPEATED => 'bg-amber-100 text-amber-800',
                                \App\Models\PromotionHistory::STATUS_GRADUATED => 'bg-sky-100 text-sky-800',
                                default => 'bg-gray-100 text-gray-700',
                            })
                            <tr>
                                <td class="px-3 py-2">
                                    <input class="student-check h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                        type="checkbox" name="student_ids[]" value="{{ $s->id }}"
                                        data-suggestion="{{ $sug }}"
                                        @checked($s->status === 'active')>
                                </td>
                                <td class="px-3 py-2 font-medium text-gray-900">{{ $s->name }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $s->admission_no ?? '—' }}</td>
                                <td class="px-3 py-2 text-right text-gray-700">{{ number_format($r['total'], 2) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700">{{ number_format($r['average'], 2) }}</td>
                                <td class="px-3 py-2 text-right text-gray-700">
                                    @if ($r['position'])
                                        {{ $r['position'] }} / {{ $r['class_size'] }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right text-gray-700">{{ $r['present'] }}</td>
                                <td class="px-3 py-2 text-right text-gray-700">{{ $r['absent'] }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $pill }}" title="{{ $r['reason'] }}">
                                        {{ ucfirst($sug) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-gray-700 capitalize">{{ $s->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Confirm promotion') }}</button>
    </form>

    @if ($history->isNotEmpty())
        <h2 class="mt-10 text-lg font-semibold text-gray-900">{{ __('Recent promotion history') }}</h2>
        <div class="mt-3 overflow-hidden rounded-lg border border-gray-200 bg-page">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-page-soft">
                    <tr>
                        <th class="px-3 py-2 font-medium text-gray-700">{{ __('When') }}</th>
                        <th class="px-3 py-2 font-medium text-gray-700">{{ __('Student') }}</th>
                        <th class="px-3 py-2 font-medium text-gray-700">{{ __('Action') }}</th>
                        <th class="px-3 py-2 font-medium text-gray-700">{{ __('From') }}</th>
                        <th class="px-3 py-2 font-medium text-gray-700">{{ __('To') }}</th>
                        <th class="px-3 py-2 font-medium text-gray-700">{{ __('By') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-page">
                    @foreach ($history as $h)
                        <tr>
                            <td class="px-3 py-2 text-gray-700">{{ $h->created_at?->toFormattedDateString() }}</td>
                            <td class="px-3 py-2 font-medium text-gray-900">{{ $h->student?->name }}</td>
                            <td class="px-3 py-2 capitalize text-gray-700">{{ $h->status }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $h->fromClass?->name ?? '—' }} · {{ $h->fromAcademicYear?->name ?? '—' }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $h->toClass?->name ?? __('Graduated') }} · {{ $h->toAcademicYear?->name ?? '—' }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $h->promoter?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <script>
        document.getElementById('check-all')?.addEventListener('change', function () {
            document.querySelectorAll('.student-check').forEach(cb => cb.checked = this.checked);
        });
        document.getElementById('check-suggested')?.addEventListener('click', function () {
            document.querySelectorAll('.student-check').forEach(cb => cb.checked = cb.dataset.suggestion === '{{ \App\Models\PromotionHistory::STATUS_PROMOTED }}');
        });
    </script>
@endsection
