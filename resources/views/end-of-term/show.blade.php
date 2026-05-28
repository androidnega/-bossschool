@extends('layouts.app')

@section('title', __('End of term'))
@section('header-title', $run->term?->name.' · '.$run->academicYear?->name)

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <div class="mb-4 flex flex-wrap items-center gap-3 text-sm text-slate-600">
        <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700">{{ $run->status }}</span>
        @if($run->closed_at)
            <span>{{ __('Closed') }}: {{ $run->closed_at->format('Y-m-d H:i') }}</span>
        @endif
        @if($run->reopened_at)
            <span>{{ __('Reopened') }}: {{ $run->reopened_at->format('Y-m-d H:i') }}</span>
        @endif
    </div>

    <form method="POST" action="{{ route('end-of-term.update', $run) }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')

        <fieldset>
            <legend class="mb-2 text-sm font-semibold text-slate-700">{{ __('Checklist') }}</legend>
            <ul class="space-y-2 text-sm text-slate-700">
                @foreach($checklistLabels as $key => $label)
                    @if($key === 'promotion_reviewed' && ! $isFinalTerm)
                        @continue
                    @endif
                    <li class="flex items-center gap-2">
                        <input id="ck-{{ $key }}" type="checkbox" name="checklist[{{ $key }}]" value="1"
                               @checked((bool) data_get($run->checklist, $key, false))
                               class="rounded border-slate-300" />
                        <label for="ck-{{ $key }}">{{ $label }}</label>
                    </li>
                @endforeach
            </ul>
        </fieldset>

        <label class="block text-sm">
            <span class="mb-1 block text-slate-700">{{ __('Notes') }}</span>
            <textarea name="notes" rows="3" class="w-full rounded-md border border-slate-300 px-2 py-1.5">{{ old('notes', $run->notes) }}</textarea>
        </label>

        @can('close', $run)
            <div class="flex flex-wrap gap-2">
                <button type="submit" name="action" value="save" class="rounded-md bg-slate-700 px-4 py-2 text-sm text-white hover:bg-slate-800">{{ __('Save checklist') }}</button>
                @unless($run->status === \App\Models\EndOfTermRun::STATUS_CLOSED)
                    <button type="submit" name="action" value="close" class="rounded-md bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-700">{{ __('Close term') }}</button>
                @endunless
            </div>
        @endcan
    </form>

    @if($run->status === \App\Models\EndOfTermRun::STATUS_CLOSED)
        @can('reopen', $run)
            <form method="POST" action="{{ route('end-of-term.reopen', $run) }}" class="mt-6 space-y-3 rounded-xl border border-amber-200 bg-amber-50 p-6">
                @csrf
                <h2 class="text-sm font-semibold text-amber-900">{{ __('Reopen term') }}</h2>
                <p class="text-xs text-amber-800">{{ __('Reopening unlocks teacher result edits. Provide a clear reason — it will be audited.') }}</p>
                <textarea name="reopen_reason" rows="2" required class="w-full rounded-md border border-amber-300 px-2 py-1.5 text-sm"></textarea>
                <button type="submit" class="rounded-md bg-amber-700 px-4 py-2 text-sm text-white hover:bg-amber-800">{{ __('Reopen term') }}</button>
            </form>
        @endcan
    @endif
@endsection
