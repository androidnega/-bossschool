@extends('layouts.app')

@section('title', __('Discipline'))
@section('header-title', __('Discipline incidents'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <div class="mb-4 flex flex-wrap items-center gap-3">
        @can('create', \App\Models\DisciplineIncident::class)
            <a href="{{ route('discipline.create') }}" class="rounded-md bg-primary px-3 py-1.5 text-sm text-white">{{ __('Record incident') }}</a>
        @endcan
        <form method="GET" class="flex items-center gap-2 text-sm">
            <select name="status" class="rounded-md border border-gray-300 px-2 py-1.5">
                <option value="">{{ __('All statuses') }}</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <select name="severity" class="rounded-md border border-gray-300 px-2 py-1.5">
                <option value="">{{ __('All severities') }}</option>
                @foreach($severities as $s)
                    <option value="{{ $s }}" @selected(request('severity') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-slate-700 px-3 py-1.5 text-sm text-white">{{ __('Filter') }}</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Date') }}</th>
                    <th class="px-4 py-3">{{ __('Student') }}</th>
                    <th class="px-4 py-3">{{ __('Category') }}</th>
                    <th class="px-4 py-3">{{ __('Severity') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($incidents as $i)
                    <tr>
                        <td class="px-4 py-3">{{ $i->incident_date?->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">{{ $i->student?->name }} <span class="text-xs text-slate-500">({{ $i->student?->schoolClass?->name }})</span></td>
                        <td class="px-4 py-3">{{ $i->category }}</td>
                        <td class="px-4 py-3">{{ $i->severity }}</td>
                        <td class="px-4 py-3">{{ $i->status }}</td>
                        <td class="px-4 py-3 text-right">
                            <a class="text-primary hover:underline" href="{{ route('discipline.show', $i) }}">{{ __('Open') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">{{ __('No incidents yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $incidents->links() }}</div>
@endsection
