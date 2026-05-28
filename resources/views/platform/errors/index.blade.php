@extends('layouts.app')

@section('title', __('Recent application errors'))
@section('header-title', __('Recent application errors'))

@section('content')
    <form method="GET" class="mb-3 flex gap-2 text-sm">
        <input type="text" name="q" value="{{ $query }}" placeholder="{{ __('Filter (e.g. ERROR, tenant_id=12)…') }}" class="w-64 rounded-md border border-slate-300 px-2 py-1.5" />
        <button class="rounded-md border border-slate-300 px-2 py-1.5">{{ __('Search') }}</button>
    </form>

    <pre class="max-h-[60vh] overflow-auto rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs leading-relaxed">@forelse($entries as $line){{ $line }}
@empty
{{ __('No log entries found.') }}
@endforelse</pre>
@endsection
