@extends('layouts.app')

@section('title', __('Activity logs'))

@section('header-title', __('Platform activity logs'))

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <h1 class="text-xl font-semibold text-slate-900">{{ __('Activity logs') }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ __('Latest first. SuperAdmin only.') }}</p>

        <form method="GET" action="{{ route('platform.activity-logs.index') }}" class="mt-6 grid gap-3 rounded-lg border border-slate-200 bg-slate-50/50 p-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-medium uppercase text-slate-500" for="search">{{ __('Search') }}</label>
                <input id="search" name="search" type="text" value="{{ $filters['search'] ?? '' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium uppercase text-slate-500" for="action">{{ __('Action') }}</label>
                <select id="action" name="action" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">{{ __('Any') }}</option>
                    @foreach ($actions as $a)
                        <option value="{{ $a }}" @selected(($filters['action'] ?? '') === $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium uppercase text-slate-500" for="tenant_id">{{ __('Tenant') }}</label>
                <select id="tenant_id" name="tenant_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">{{ __('Any') }}</option>
                    @foreach ($tenants as $t)
                        <option value="{{ $t->id }}" @selected((string) ($filters['tenant_id'] ?? '') === (string) $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium uppercase text-slate-500" for="actor_id">{{ __('Actor') }}</label>
                <select id="actor_id" name="actor_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">{{ __('Any') }}</option>
                    @foreach ($actors as $ac)
                        <option value="{{ $ac->id }}" @selected((string) ($filters['actor_id'] ?? '') === (string) $ac->id)>{{ $ac->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium uppercase text-slate-500" for="from">{{ __('From') }}</label>
                <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium uppercase text-slate-500" for="to">{{ __('To') }}</label>
                <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-3">
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">{{ __('Apply filters') }}</button>
                <a href="{{ route('platform.activity-logs.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">{{ __('Reset') }}</a>
            </div>
        </form>

        <div class="mt-8 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2">{{ __('When') }}</th>
                        <th class="px-4 py-2">{{ __('Action') }}</th>
                        <th class="px-4 py-2">{{ __('Actor') }}</th>
                        <th class="px-4 py-2">{{ __('Tenant') }}</th>
                        <th class="px-4 py-2">{{ __('Description') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 py-2 text-slate-600">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-2 font-mono text-xs text-slate-900">{{ $log->action }}</td>
                            <td class="px-4 py-2 text-slate-700">{{ $log->actor_name ?? '—' }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $log->tenant?->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-slate-700">{{ \Illuminate\Support\Str::limit((string) $log->description, 80) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">{{ __('No logs match.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
@endsection
