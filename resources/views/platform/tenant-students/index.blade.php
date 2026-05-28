@extends('layouts.app')

@section('title', __('Students'))

@section('header-title', __('Tenant students'))

@section('content')
    @include('platform.tenants._control-nav', ['tenant' => $tenant])

    <h1 class="text-xl font-semibold text-slate-900">{{ __('Students') }} — {{ $tenant->name }}</h1>

    <form method="GET" class="mt-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4">
        <div>
            <label class="block text-xs font-medium text-slate-600" for="search">{{ __('Search') }}</label>
            <input id="search" name="search" value="{{ $filters['search'] }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600" for="class_id">{{ __('Class') }}</label>
            <select id="class_id" name="class_id" class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">{{ __('All') }}</option>
                @foreach ($classes as $c)
                    <option value="{{ $c->id }}" @selected((string) ($filters['class_id'] ?? '') === (string) $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600" for="status">{{ __('Status') }}</label>
            <select id="status" name="status" class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">{{ __('All') }}</option>
                @foreach (['active', 'inactive', 'graduated'] as $st)
                    <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">{{ __('Filter') }}</button>
    </form>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Name') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Class') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Portal') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($students as $s)
                    <tr>
                        <td class="px-4 py-3">{{ $s->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $s->schoolClass?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $s->status }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $s->linkedUser ? __('Yes') : __('No') }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('platform.tenants.students.show', [$tenant, $s]) }}" class="text-teal-800 hover:underline">{{ __('View') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $students->links() }}</div>
@endsection
