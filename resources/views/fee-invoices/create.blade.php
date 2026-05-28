@extends('layouts.app')

@section('title', __('New invoice'))

@section('header-title', 'Fees')

@section('content')
    @include('finances._subnav')

    <div class="mb-6">
        <a href="{{ route('fee-invoices.index') }}" class="text-sm font-medium text-secondary hover:text-primary">← {{ __('Back to invoices') }}</a>
        <h1 class="mt-2 text-2xl font-semibold text-primary">{{ __('New fee invoice') }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ __('Create a draft invoice. Add items, then issue it.') }}</p>
    </div>

    <form method="POST" action="{{ route('fee-invoices.store') }}" class="max-w-2xl space-y-4 rounded-lg border border-gray-200 bg-page p-6">
        @csrf

        <div>
            <label for="student_id" class="block text-sm font-medium text-gray-700">{{ __('Student') }} <span class="text-red-600">*</span></label>
            <select id="student_id" name="student_id" required class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                <option value="">—</option>
                @foreach ($students as $s)
                    <option value="{{ $s->id }}" @selected((string) old('student_id') === (string) $s->id)>{{ $s->name }} @if($s->schoolClass) ({{ $s->schoolClass->name }}@if($s->schoolClass->section) — {{ $s->schoolClass->section }}@endif)@endif</option>
                @endforeach
            </select>
            @error('student_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('Academic year') }}</label>
                <select name="academic_year_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                    <option value="">—</option>
                    @foreach ($years as $y)
                        <option value="{{ $y->id }}" @selected(old('academic_year_id', $year?->id) === $y->id)>{{ $y->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('Term') }}</label>
                <select name="term_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                    <option value="">—</option>
                    @foreach ($terms as $t)
                        <option value="{{ $t->id }}" @selected(old('term_id', $term?->id) === $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('Class') }}</label>
                <select name="class_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                    <option value="">{{ __("Use student's current class") }}</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}" @selected((string) old('class_id') === (string) $c->id)>{{ $c->name }}@if($c->section) — {{ $c->section }}@endif</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('Due date') }}</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="use_class_template" value="1" checked>
            {{ __("Pre-fill with class fee structure") }}
        </label>

        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
            <textarea name="notes" rows="2" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3 border-t border-gray-200 pt-6">
            <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Create draft invoice') }}</button>
            <a href="{{ route('fee-invoices.index') }}" class="rounded-md border border-gray-300 bg-page px-4 py-2 text-sm font-medium text-gray-700 hover:bg-page-soft">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection
