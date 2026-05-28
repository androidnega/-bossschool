@extends('layouts.app')

@section('title', __('Report card sheet'))

@section('header-title', __('Report cards'))

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-primary">{{ __('Report card sheet') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Pick a class, academic year and term, then open a student to enter remarks, conduct, attendance summary, fees and signatures.') }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('report-card-meta.index') }}" class="mt-4 grid gap-3 rounded-lg border border-gray-200 bg-page p-4 sm:grid-cols-4">
        <div>
            <label for="class_id" class="block text-xs font-medium uppercase text-secondary">{{ __('Class') }}</label>
            <select name="class_id" id="class_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                @foreach ($classes as $c)
                    <option value="{{ $c->id }}" @selected((int) $classId === (int) $c->id)>{{ $c->name }}@if ($c->section) — {{ $c->section }} @endif</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="academic_year_id" class="block text-xs font-medium uppercase text-secondary">{{ __('Academic year') }}</label>
            <select name="academic_year_id" id="academic_year_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                @foreach ($years as $y)
                    <option value="{{ $y->id }}" @selected((int) $yearId === (int) $y->id)>{{ $y->name }}@if ($y->is_current) ({{ __('current') }})@endif</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="term_id" class="block text-xs font-medium uppercase text-secondary">{{ __('Term') }}</label>
            <select name="term_id" id="term_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                @foreach ($terms as $t)
                    <option value="{{ $t->id }}" @selected((int) $termId === (int) $t->id)>{{ $t->name }}@if ($t->is_current) ({{ __('current') }})@endif</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="rounded-md border border-secondary/60 bg-page px-4 py-2 text-sm font-medium text-secondary hover:bg-page-soft">{{ __('Apply filters') }}</button>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-page">
        @if ($students->isEmpty())
            <div class="p-6">
                <x-empty-state :title="__('No students yet')" :message="__('Pick a class that has students enrolled.')" />
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-page-soft">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Student') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Admission no.') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Meta saved?') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-page">
                    @foreach ($students as $s)
                        @php($has = $existing->has($s->id))
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $s->name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $s->admission_no ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                @if ($has)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">{{ __('saved') }}</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">{{ __('not yet') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('report-card-meta.edit', ['student' => $s->id, 'academic_year_id' => $yearId, 'term_id' => $termId]) }}" class="text-primary hover:underline">{{ __('Open sheet') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
