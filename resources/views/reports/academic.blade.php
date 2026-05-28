@extends('layouts.app')

@section('title', __('Academic report'))

@section('header-title', __('Reports'))

@section('content')
    @include('reports._subnav')

    <h1 class="text-2xl font-semibold text-primary">{{ __('Academic report') }}</h1>
    <p class="mt-1 text-sm text-gray-600">{{ __('Performance across subjects and students.') }}</p>

    <div class="mt-8 rounded-lg border border-gray-200 bg-page p-6">
        <h2 class="text-lg font-semibold text-primary">{{ __('Average score per subject') }}</h2>
        <div class="mt-4 space-y-3">
            @forelse ($avgBySubject as $row)
                @php
                    $avg = (float) $row->avg_total;
                    $pct = $subjectAvgMax > 0 ? round(($avg / $subjectAvgMax) * 100) : 0;
                @endphp
                <div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-800">{{ $row->subject?->name ?? __('Unknown') }}</span>
                        <span class="tabular-nums font-medium text-gray-900">{{ number_format($avg, 2) }}</span>
                    </div>
                    <div class="mt-1 h-2 overflow-hidden rounded bg-stone-200">
                        <div class="h-full bg-stone-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-600">{{ __('No results yet.') }}</p>
            @endforelse
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-page p-6">
            <h2 class="text-lg font-semibold text-primary">{{ __('Top students') }}</h2>
            <p class="mt-1 text-xs text-gray-500">{{ __('By average total across subjects with results.') }}</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-gray-700">
                        <tr>
                            <th class="py-2 pr-2 font-medium">#</th>
                            <th class="py-2 pr-2 font-medium">{{ __('Student') }}</th>
                            <th class="py-2 font-medium text-right">{{ __('Avg') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($topStudents as $i => $row)
                            <tr>
                                <td class="py-2 pr-2 text-gray-500">{{ $i + 1 }}</td>
                                <td class="py-2 pr-2 font-medium text-gray-900">{{ $row->student?->name ?? '—' }}</td>
                                <td class="py-2 text-right tabular-nums text-gray-900">{{ number_format((float) $row->avg_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-gray-600">{{ __('No data.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-page p-6">
            <h2 class="text-lg font-semibold text-primary">{{ __('Weak subjects') }}</h2>
            <p class="mt-1 text-xs text-gray-500">{{ __('Subjects with the lowest average scores (up to five).') }}</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-gray-700">
                        <tr>
                            <th class="py-2 pr-2 font-medium">{{ __('Subject') }}</th>
                            <th class="py-2 font-medium text-right">{{ __('Avg') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($weakSubjects as $row)
                            <tr>
                                <td class="py-2 pr-2 text-gray-900">{{ $row->subject?->name ?? '—' }}</td>
                                <td class="py-2 text-right tabular-nums text-gray-900">{{ number_format((float) $row->avg_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-4 text-gray-600">{{ __('No data.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
