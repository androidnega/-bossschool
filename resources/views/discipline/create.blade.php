@extends('layouts.app')

@section('title', __('Record incident'))
@section('header-title', __('Record discipline incident'))

@section('content')
    <form method="POST" action="{{ route('discipline.store') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @if($currentYear)<input type="hidden" name="academic_year_id" value="{{ $currentYear->id }}" />@endif
        @if($currentTerm)<input type="hidden" name="term_id" value="{{ $currentTerm->id }}" />@endif

        <label class="block text-sm">
            <span class="mb-1 block text-slate-700">{{ __('Student') }}</span>
            <select name="student_id" required class="w-full rounded-md border border-slate-300 px-2 py-1.5">
                @foreach($students as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </label>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Incident date') }}</span>
                <input type="date" name="incident_date" required value="{{ old('incident_date', now()->toDateString()) }}" class="w-full rounded-md border border-slate-300 px-2 py-1.5" />
            </label>
            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Category') }}</span>
                <input type="text" name="category" required maxlength="64" placeholder="e.g. fighting, lateness" class="w-full rounded-md border border-slate-300 px-2 py-1.5" />
            </label>
            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Severity') }}</span>
                <select name="severity" required class="w-full rounded-md border border-slate-300 px-2 py-1.5">
                    @foreach($severities as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </label>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="parent_notified" value="1" class="rounded border-slate-300" />
                <span>{{ __('Parent notified') }}</span>
            </label>
        </div>

        <label class="block text-sm">
            <span class="mb-1 block text-slate-700">{{ __('Description') }}</span>
            <textarea name="description" rows="4" required class="w-full rounded-md border border-slate-300 px-2 py-1.5"></textarea>
        </label>

        <label class="block text-sm">
            <span class="mb-1 block text-slate-700">{{ __('Action taken') }}</span>
            <textarea name="action_taken" rows="2" class="w-full rounded-md border border-slate-300 px-2 py-1.5"></textarea>
        </label>

        <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm text-white hover:bg-primary/95">{{ __('Save incident') }}</button>
    </form>
@endsection
