@extends('layouts.app')

@section('title', __('Bulk report-card meta'))
@section('header-title', __('Bulk report-card meta'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('report-card-meta.bulk.update') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Class') }}</span>
                <select name="class_id" required class="w-full rounded-md border border-slate-300 px-2 py-1.5">
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected($classId === $class->id)>{{ $class->name }} {{ $class->section }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Academic year') }}</span>
                <select name="academic_year_id" required class="w-full rounded-md border border-slate-300 px-2 py-1.5">
                    @foreach($years as $year)
                        <option value="{{ $year->id }}" @selected($yearId === $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Term') }}</span>
                <select name="term_id" required class="w-full rounded-md border border-slate-300 px-2 py-1.5">
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" @selected($termId === $term->id)>{{ $term->name }} ({{ $term->academicYear?->name }})</option>
                    @endforeach
                </select>
            </label>
        </div>

        @if(in_array('days_school_opened', $editableFields, true))
            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Days school opened') }}</span>
                <input type="number" min="0" max="200" name="days_school_opened" class="w-48 rounded-md border border-slate-300 px-2 py-1.5" />
            </label>
        @endif

        @if(in_array('class_teacher_remark', $editableFields, true))
            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Class-teacher remark (applied to every student)') }}</span>
                <textarea name="class_teacher_remark" rows="2" class="w-full rounded-md border border-slate-300 px-2 py-1.5"></textarea>
            </label>
        @endif

        @if(in_array('head_teacher_remark', $editableFields, true))
            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Head-teacher remark') }}</span>
                <textarea name="head_teacher_remark" rows="2" class="w-full rounded-md border border-slate-300 px-2 py-1.5"></textarea>
            </label>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @if(in_array('next_term_fee', $editableFields, true))
                <label class="block text-sm">
                    <span class="mb-1 block text-slate-700">{{ __('Next-term fee (GH₵)') }}</span>
                    <input type="number" step="0.01" min="0" name="next_term_fee" class="w-full rounded-md border border-slate-300 px-2 py-1.5" />
                </label>
            @endif
            @if(in_array('vacation_date', $editableFields, true))
                <label class="block text-sm">
                    <span class="mb-1 block text-slate-700">{{ __('Vacation date') }}</span>
                    <input type="date" name="vacation_date" class="w-full rounded-md border border-slate-300 px-2 py-1.5" />
                </label>
            @endif
            @if(in_array('reopening_date', $editableFields, true))
                <label class="block text-sm">
                    <span class="mb-1 block text-slate-700">{{ __('Reopening date') }}</span>
                    <input type="date" name="reopening_date" class="w-full rounded-md border border-slate-300 px-2 py-1.5" />
                </label>
            @endif
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="prefill_attendance" value="1" class="rounded border-slate-300" />
            <span>{{ __('Prefill attendance summary from attendance records') }}</span>
        </label>

        <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm text-white hover:bg-primary/95">{{ __('Apply to class') }}</button>
    </form>
@endsection
