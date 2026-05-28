@extends('layouts.app')

@section('title', __('End of term'))
@section('header-title', __('End-of-term workflow'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    @can('create', \App\Models\EndOfTermRun::class)
        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-3 text-base font-semibold text-slate-900">{{ __('Start end-of-term workflow') }}</h2>
            <form method="POST" action="{{ route('end-of-term.store') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <label class="block text-sm">
                    <span class="mb-1 block text-slate-700">{{ __('Academic year') }}</span>
                    <select name="academic_year_id" required class="rounded-md border border-gray-300 px-2 py-1.5">
                        @foreach($years as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-sm">
                    <span class="mb-1 block text-slate-700">{{ __('Term') }}</span>
                    <select name="term_id" required class="rounded-md border border-gray-300 px-2 py-1.5">
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }} ({{ $term->academicYear?->name ?? '—' }})</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="rounded-md bg-primary px-3 py-1.5 text-sm text-white hover:bg-primary/95">{{ __('Start workflow') }}</button>
            </form>
        </div>
    @endcan

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Term') }}</th>
                    <th class="px-4 py-3">{{ __('Academic year') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Closed at') }}</th>
                    <th class="px-4 py-3">{{ __('Initiated by') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($runs as $run)
                    <tr>
                        <td class="px-4 py-3">{{ $run->term?->name }}</td>
                        <td class="px-4 py-3">{{ $run->academicYear?->name }}</td>
                        <td class="px-4 py-3">{{ $run->status }}</td>
                        <td class="px-4 py-3">{{ $run->closed_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $run->initiator?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a class="text-primary hover:underline" href="{{ route('end-of-term.show', $run) }}">{{ __('Open') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">{{ __('No workflows yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $runs->links() }}</div>
@endsection
