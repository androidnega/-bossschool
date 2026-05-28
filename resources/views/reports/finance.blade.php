@extends('layouts.app')

@section('title', __('Finance report'))

@section('header-title', __('Reports'))

@section('content')
    @include('reports._subnav')

    <h1 class="text-2xl font-semibold text-primary">{{ __('Finance report') }}</h1>
    <p class="mt-1 text-sm text-gray-600">{{ __('Filter collections by payment date.') }}</p>

    <div class="mt-6 rounded-lg border border-gray-200 bg-page p-4">
        <form method="GET" action="{{ route('reports.finance') }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700">{{ __('From') }}</label>
                <input id="date_from" name="date_from" type="date" value="{{ $from->format('Y-m-d') }}"
                    class="mt-1 rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700">{{ __('To') }}</label>
                <input id="date_to" name="date_to" type="date" value="{{ $to->format('Y-m-d') }}"
                    class="mt-1 rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Apply') }}</button>
                <a href="{{ route('reports.finance') }}" class="rounded-md border border-gray-300 bg-page px-4 py-2 text-sm font-medium text-gray-700 hover:bg-page-soft">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-stone-200/80 bg-card-mist p-6 ring-1 ring-stone-200/40">
            <p class="text-sm font-medium text-stone-600">{{ __('Total revenue (period)') }}</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums text-stone-900">{{ cedis($totalRevenue) }}</p>
            <p class="mt-1 text-xs text-stone-500">{{ $from->format('M j, Y') }} — {{ $to->format('M j, Y') }}</p>
        </div>
        <div class="rounded-2xl border border-stone-200/80 bg-card-shell p-6 ring-1 ring-stone-200/40">
            <p class="text-sm font-medium text-stone-600">{{ __('Outstanding fees') }}</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums text-stone-900">{{ cedis($outstandingFees) }}</p>
            <p class="mt-1 text-xs text-stone-500">{{ __('All scheduled fees minus all payments recorded.') }}</p>
        </div>
    </div>

    <div class="mt-8 rounded-lg border border-gray-200 bg-page p-6">
        <h2 class="text-lg font-semibold text-primary">{{ __('Revenue by term') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('Estimated share of period revenue by term, weighted by fee schedule per term.') }}</p>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-gray-700">
                    <tr>
                        <th class="py-2 pr-4 font-medium">{{ __('Term') }}</th>
                        <th class="py-2 pr-4 font-medium text-right">{{ __('Fee schedule') }}</th>
                        <th class="py-2 font-medium text-right">{{ __('Allocated revenue') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($revenueByTerm as $row)
                        @php
                            $pct = $maxAllocated > 0 ? round(($row['revenue_allocated'] / $maxAllocated) * 100) : 0;
                        @endphp
                        <tr>
                            <td class="py-3 pr-4 font-medium text-gray-900">{{ $row['term']->name }}</td>
                            <td class="py-3 pr-4 text-right tabular-nums text-stone-700">{{ cedis($row['fee_sum']) }}</td>
                            <td class="py-3">
                                <div class="text-right tabular-nums font-medium text-stone-900">{{ cedis($row['revenue_allocated']) }}</div>
                                <div class="mt-1 ml-auto h-2 w-full max-w-xs overflow-hidden rounded bg-stone-200">
                                    <div class="h-full bg-stone-500" style="width: {{ $pct }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
