@extends('layouts.app')

@section('title', __('Staff attendance'))
@section('header-title', __('Staff attendance'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <form method="GET" class="flex items-center gap-2 text-sm">
            <label class="block"><span class="mr-1 text-slate-700">{{ __('Date') }}</span>
                <input type="date" name="date" value="{{ $date }}" class="rounded-md border border-slate-300 px-2 py-1.5" />
            </label>
            <button type="submit" class="rounded-md bg-slate-700 px-3 py-1.5 text-sm text-white">{{ __('View') }}</button>
        </form>
        <a href="{{ route('staff-attendance.create', ['date' => $date]) }}" class="rounded-md bg-primary px-3 py-1.5 text-sm text-white">{{ __('Mark for this date') }}</a>
    </div>

    <div class="mb-4 flex flex-wrap gap-3 text-xs text-slate-600">
        @foreach($report as $status => $count)
            <span class="rounded-full bg-slate-100 px-3 py-1">{{ $status }}: {{ $count }}</span>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Staff') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Remarks') }}</th>
                    <th class="px-4 py-3">{{ __('Marked by') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rows as $row)
                    <tr>
                        <td class="px-4 py-3">{{ $row->staff?->name }}</td>
                        <td class="px-4 py-3">{{ $row->status }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $row->remarks }}</td>
                        <td class="px-4 py-3">{{ $row->marker?->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">{{ __('No staff attendance marked for this date.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
