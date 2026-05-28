@extends('layouts.app')

@section('title', __('Report card sheet'))

@section('header-title', __('Report cards'))

@php
    $can = fn (string $field) => in_array($field, $editableFields, true);
@endphp

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-primary">{{ __('Report card sheet') }} — {{ $student->name }}</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ $student->schoolClass?->name }}@if ($student->schoolClass?->section) — {{ $student->schoolClass->section }}@endif
                · {{ __('Admission no.') }} {{ $student->admission_no ?? '—' }}
            </p>
        </div>
        <a href="{{ route('report-card-meta.index', ['class_id' => $student->class_id, 'academic_year_id' => $year?->id, 'term_id' => $term?->id]) }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to list') }}</a>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('report-card-meta.edit', $student) }}" class="mt-4 grid gap-3 rounded-lg border border-gray-200 bg-page p-4 sm:grid-cols-3">
        <div>
            <label for="academic_year_id" class="block text-xs font-medium uppercase text-secondary">{{ __('Academic year') }}</label>
            <select name="academic_year_id" id="academic_year_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                @foreach ($years as $y)
                    <option value="{{ $y->id }}" @selected((int) $year?->id === (int) $y->id)>{{ $y->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="term_id" class="block text-xs font-medium uppercase text-secondary">{{ __('Term') }}</label>
            <select name="term_id" id="term_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                @foreach ($terms as $t)
                    <option value="{{ $t->id }}" @selected((int) $term?->id === (int) $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="rounded-md border border-secondary/60 bg-page px-4 py-2 text-sm font-medium text-secondary hover:bg-page-soft">{{ __('Switch period') }}</button>
        </div>
    </form>

    <form method="POST" action="{{ route('report-card-meta.update', ['student' => $student->id, 'academic_year_id' => $year?->id, 'term_id' => $term?->id]) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <fieldset class="rounded-lg border border-gray-200 bg-page p-5">
            <legend class="px-2 text-sm font-semibold text-gray-700">{{ __('Attendance summary') }}</legend>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach (['days_school_opened' => __('Days school opened'), 'days_present' => __('Days present'), 'days_absent' => __('Days absent')] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                        <input type="number" min="0" max="400" name="{{ $field }}" id="{{ $field }}"
                            value="{{ old($field, $meta?->{$field}) }}"
                            @disabled(! $can($field))
                            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm @error($field) border-red-500 @enderror">
                        @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="rounded-lg border border-gray-200 bg-page p-5">
            <legend class="px-2 text-sm font-semibold text-gray-700">{{ __('Conduct, attitude, interest') }}</legend>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach (['conduct' => __('Conduct'), 'attitude' => __('Attitude'), 'interest' => __('Interest')] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                        <input type="text" maxlength="64" name="{{ $field }}" id="{{ $field }}"
                            value="{{ old($field, $meta?->{$field}) }}"
                            @disabled(! $can($field))
                            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm @error($field) border-red-500 @enderror">
                        @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="rounded-lg border border-gray-200 bg-page p-5">
            <legend class="px-2 text-sm font-semibold text-gray-700">{{ __('Remarks') }}</legend>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="class_teacher_remark" class="block text-sm font-medium text-gray-700">{{ __('Class teacher remark') }}</label>
                    <textarea name="class_teacher_remark" id="class_teacher_remark" rows="4" maxlength="1000"
                        @disabled(! $can('class_teacher_remark'))
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm @error('class_teacher_remark') border-red-500 @enderror">{{ old('class_teacher_remark', $meta?->class_teacher_remark) }}</textarea>
                    @error('class_teacher_remark')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="head_teacher_remark" class="block text-sm font-medium text-gray-700">{{ __('Headteacher remark') }}</label>
                    <textarea name="head_teacher_remark" id="head_teacher_remark" rows="4" maxlength="1000"
                        @disabled(! $can('head_teacher_remark'))
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm @error('head_teacher_remark') border-red-500 @enderror">{{ old('head_teacher_remark', $meta?->head_teacher_remark) }}</textarea>
                    @unless ($can('head_teacher_remark'))
                        <p class="mt-1 text-xs text-gray-500">{{ __('Only Admin or Proprietor can edit this field.') }}</p>
                    @endunless
                    @error('head_teacher_remark')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </fieldset>

        <fieldset class="rounded-lg border border-gray-200 bg-page p-5">
            <legend class="px-2 text-sm font-semibold text-gray-700">{{ __('Next term + signatures') }}</legend>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="next_term_fee" class="block text-sm font-medium text-gray-700">{{ __('Next term fee (GHS)') }}</label>
                    <input type="number" step="0.01" min="0" max="99999999.99" name="next_term_fee" id="next_term_fee"
                        value="{{ old('next_term_fee', $meta?->next_term_fee) }}"
                        @disabled(! $can('next_term_fee'))
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm @error('next_term_fee') border-red-500 @enderror">
                    @unless ($can('next_term_fee'))
                        <p class="mt-1 text-xs text-gray-500">{{ __('Only Admin or Proprietor can edit this field.') }}</p>
                    @endunless
                    @error('next_term_fee')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="vacation_date" class="block text-sm font-medium text-gray-700">{{ __('Vacation date') }}</label>
                    <input type="date" name="vacation_date" id="vacation_date"
                        value="{{ old('vacation_date', optional($meta?->vacation_date)->toDateString()) }}"
                        @disabled(! $can('vacation_date'))
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm @error('vacation_date') border-red-500 @enderror">
                    @error('vacation_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="reopening_date" class="block text-sm font-medium text-gray-700">{{ __('Reopening date') }}</label>
                    <input type="date" name="reopening_date" id="reopening_date"
                        value="{{ old('reopening_date', optional($meta?->reopening_date)->toDateString()) }}"
                        @disabled(! $can('reopening_date'))
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm @error('reopening_date') border-red-500 @enderror">
                    @error('reopening_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="class_teacher_signature" class="block text-sm font-medium text-gray-700">{{ __('Class teacher signature') }}</label>
                    <input type="text" maxlength="191" name="class_teacher_signature" id="class_teacher_signature"
                        value="{{ old('class_teacher_signature', $meta?->class_teacher_signature) }}"
                        @disabled(! $can('class_teacher_signature'))
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm @error('class_teacher_signature') border-red-500 @enderror">
                    @error('class_teacher_signature')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="head_teacher_signature" class="block text-sm font-medium text-gray-700">{{ __('Headteacher signature') }}</label>
                    <input type="text" maxlength="191" name="head_teacher_signature" id="head_teacher_signature"
                        value="{{ old('head_teacher_signature', $meta?->head_teacher_signature) }}"
                        @disabled(! $can('head_teacher_signature'))
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm @error('head_teacher_signature') border-red-500 @enderror">
                    @unless ($can('head_teacher_signature'))
                        <p class="mt-1 text-xs text-gray-500">{{ __('Only Admin or Proprietor can edit this field.') }}</p>
                    @endunless
                    @error('head_teacher_signature')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </fieldset>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Save sheet') }}</button>
            <a href="{{ route('students.report-card', ['student' => $student->id, 'academic_year_id' => $year?->id, 'term_id' => $term?->id]) }}" class="text-sm text-gray-600 hover:underline">{{ __('Preview report card') }}</a>
        </div>
    </form>
@endsection
