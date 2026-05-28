@extends('layouts.app')

@section('title', __('Results'))

@section('header-title', 'Results')

@section('content')
    @include('results._subnav')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-primary">{{ __('Results') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Scores by student and subject.') }}</p>
        </div>
        @can('create', \App\Models\Result::class)
            <a href="{{ route('results.create') }}" class="inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Add result') }}</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('results.index') }}" class="mt-4 grid gap-3 rounded-lg border border-gray-200 bg-page p-4 sm:grid-cols-3">
        <div>
            <label for="filter_academic_year_id" class="block text-xs font-medium uppercase text-secondary">{{ __('Academic year') }}</label>
            <select name="academic_year_id" id="filter_academic_year_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                <option value="">{{ __('All') }}</option>
                @foreach ($years as $year)
                    <option value="{{ $year->id }}" @selected((int) $yearId === (int) $year->id)>{{ $year->name }}@if($year->is_current) ({{ __('current') }})@endif</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filter_term_id" class="block text-xs font-medium uppercase text-secondary">{{ __('Term') }}</label>
            <select name="term_id" id="filter_term_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                <option value="">{{ __('All') }}</option>
                @foreach ($terms as $term)
                    <option value="{{ $term->id }}" @selected((int) $termId === (int) $term->id)>{{ $term->name }} · {{ $term->academicYear?->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="rounded-md border border-secondary/60 bg-page px-4 py-2 text-sm font-medium text-secondary hover:bg-page-soft">{{ __('Apply filter') }}</button>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-page">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-page-soft">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Student') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Subject') }}</th>
                        <th class="hidden px-4 py-3 font-medium text-gray-700 lg:table-cell text-right">{{ __('Test') }}</th>
                        <th class="hidden px-4 py-3 font-medium text-gray-700 lg:table-cell text-right">{{ __('Mid') }}</th>
                        <th class="hidden px-4 py-3 font-medium text-gray-700 lg:table-cell text-right">{{ __('Exam') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Total') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Grade') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-page">
                    @forelse ($results as $row)
                        <tr class="hover:bg-page-soft/80">
                            <td class="px-4 py-3">
                                <a href="{{ route('students.show', $row->student_id) }}" class="font-medium text-primary hover:underline">{{ $row->student?->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $row->subject?->name }}</td>
                            <td class="hidden px-4 py-3 text-right tabular-nums text-gray-900 lg:table-cell">{{ $row->class_test !== null ? number_format((float) $row->class_test, 2) : '—' }}</td>
                            <td class="hidden px-4 py-3 text-right tabular-nums text-gray-900 lg:table-cell">{{ $row->midterm !== null ? number_format((float) $row->midterm, 2) : '—' }}</td>
                            <td class="hidden px-4 py-3 text-right tabular-nums text-gray-900 lg:table-cell">{{ $row->exam !== null ? number_format((float) $row->exam, 2) : '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium text-gray-900">{{ number_format((float) $row->total, 2) }}</td>
                            <td class="px-4 py-3"><span class="font-semibold text-primary">{{ $row->grade }}</span></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @can('viewAny', \App\Models\Result::class)
                                    <a href="{{ route('students.report-card', $row->student_id) }}" class="text-primary hover:underline">{{ __('Card') }}</a>
                                @endcan
                                @can('update', $row)
                                    <span class="text-gray-300">·</span>
                                    <a href="{{ route('results.edit', $row) }}" class="text-primary hover:underline">{{ __('Edit') }}</a>
                                @endcan
                                @can('delete', $row)
                                    <span class="text-gray-300">·</span>
                                    <form action="{{ route('results.destroy', $row) }}" method="POST" class="inline" onsubmit="return confirm({{ json_encode(__('Remove this result?')) }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-600">{{ __('No results yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($results->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $results->links() }}</div>
        @endif
    </div>
@endsection
