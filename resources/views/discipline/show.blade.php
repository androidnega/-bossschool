@extends('layouts.app')

@section('title', __('Incident'))
@section('header-title', $incident->category)

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-700">
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div><dt class="text-xs text-slate-500">{{ __('Student') }}</dt><dd>{{ $incident->student?->name }} ({{ $incident->student?->schoolClass?->name }})</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Date') }}</dt><dd>{{ $incident->incident_date?->format('Y-m-d') }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Severity') }}</dt><dd>{{ $incident->severity }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Status') }}</dt><dd>{{ $incident->status }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs text-slate-500">{{ __('Description') }}</dt><dd class="whitespace-pre-wrap">{{ $incident->description }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs text-slate-500">{{ __('Action taken') }}</dt><dd class="whitespace-pre-wrap">{{ $incident->action_taken ?? '—' }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Reported by') }}</dt><dd>{{ $incident->reporter?->name }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Resolved by') }}</dt><dd>{{ $incident->resolver?->name ?? '—' }}</dd></div>
        </dl>
    </div>

    @can('update', $incident)
        <form method="POST" action="{{ route('discipline.update', $incident) }}" class="mt-6 space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            @csrf
            @method('PUT')
            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Action taken') }}</span>
                <textarea name="action_taken" rows="2" class="w-full rounded-md border border-slate-300 px-2 py-1.5">{{ old('action_taken', $incident->action_taken) }}</textarea>
            </label>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="parent_notified" value="1" @checked($incident->parent_notified) class="rounded border-slate-300" />
                <span>{{ __('Parent notified') }}</span>
            </label>
            @can('resolve', $incident)
                <label class="block text-sm">
                    <span class="mb-1 block text-slate-700">{{ __('Status') }}</span>
                    <select name="status" class="rounded-md border border-slate-300 px-2 py-1.5">
                        @foreach(\App\Models\DisciplineIncident::STATUSES as $s)
                            <option value="{{ $s }}" @selected($incident->status === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </label>
            @endcan
            <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm text-white hover:bg-primary/95">{{ __('Save changes') }}</button>
        </form>
    @endcan
@endsection
