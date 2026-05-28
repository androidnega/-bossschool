@extends('layouts.app')

@section('title', __('Audit log'))
@section('header-title', __('Audit log'))

@section('content')
    @if(! empty($flags))
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
            <h2 class="text-sm font-semibold text-amber-900">{{ __('Suspicious activity in the last 7 days') }}</h2>
            <ul class="mt-2 list-disc pl-5 text-sm text-amber-900">
                @foreach($flags as $f)
                    <li><strong>{{ str_replace('_', ' ', $f['kind']) }}:</strong> {{ $f['message'] }} ({{ strtoupper($f['severity']) }})</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" class="mb-4 grid grid-cols-2 gap-2 text-sm md:grid-cols-6">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search') }}" class="rounded-md border border-slate-300 px-2 py-1.5" />
        <select name="action" class="rounded-md border border-slate-300 px-2 py-1.5">
            <option value="">{{ __('Any action') }}</option>
            @foreach($actions as $a)
                <option value="{{ $a }}" @selected(($filters['action'] ?? '') === $a)>{{ $a }}</option>
            @endforeach
        </select>
        <select name="actor_id" class="rounded-md border border-slate-300 px-2 py-1.5">
            <option value="">{{ __('Any actor') }}</option>
            @foreach($actors as $u)
                <option value="{{ $u->id }}" @selected((int)($filters['actor_id'] ?? 0) === (int)$u->id)>{{ $u->name }}</option>
            @endforeach
        </select>
        <input type="text" name="module" value="{{ $filters['module'] ?? '' }}" placeholder="{{ __('module prefix') }}" class="rounded-md border border-slate-300 px-2 py-1.5" />
        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="rounded-md border border-slate-300 px-2 py-1.5" />
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="rounded-md border border-slate-300 px-2 py-1.5" />
        <div class="md:col-span-6 flex gap-2">
            <button class="rounded-md bg-slate-700 px-3 py-1.5 text-sm text-white">{{ __('Filter') }}</button>
            <a href="{{ route('audit-logs.export', request()->query()) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">{{ __('Export CSV') }}</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('When') }}</th>
                    <th class="px-4 py-3">{{ __('Actor') }}</th>
                    <th class="px-4 py-3">{{ __('Action') }}</th>
                    <th class="px-4 py-3">{{ __('Description') }}</th>
                    <th class="px-4 py-3">{{ __('Target') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-xs">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-xs">{{ $log->actor_name }} <span class="text-slate-400">({{ $log->actor_role }})</span></td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $log->action }}</td>
                        <td class="px-4 py-3 text-xs">{{ $log->description }}</td>
                        <td class="px-4 py-3 text-xs">{{ class_basename((string) $log->target_type) }} #{{ $log->target_id }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">{{ __('No audit entries.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $logs->links() }}</div>
@endsection
