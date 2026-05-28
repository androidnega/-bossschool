@extends('layouts.app')

@section('title', __('Academic years'))

@section('header-title', __('Settings'))

@section('content')
    @include('settings._subnav')

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-primary">{{ __('Academic years') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Define each academic year (e.g. 2025/2026) and mark which one is current. Terms, results, attendance, and report cards use this.') }}</p>
        </div>
        @can('create', \App\Models\AcademicYear::class)
            <a href="{{ route('academic-years.create') }}" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Add academic year') }}</a>
        @endcan
    </div>

    @if ($currentYear)
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">
            <strong>{{ __('Current academic year') }}:</strong> {{ $currentYear->name }}
            @if ($currentYear->starts_on && $currentYear->ends_on)
                · {{ $currentYear->starts_on?->toFormattedDateString() }} → {{ $currentYear->ends_on?->toFormattedDateString() }}
            @endif
        </div>
    @else
        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50/60 px-4 py-3 text-sm text-amber-900">
            {{ __('No current academic year is set. Create one or mark an existing year as current.') }}
        </div>
    @endif

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-page">
        @if ($years->isEmpty())
            <div class="p-6">
                <x-empty-state :title="__('No academic years yet')" :message="__('Create your first academic year to begin tracking terms and results.')" />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-page-soft">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Name') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Starts') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Ends') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Status') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-page">
                        @foreach ($years as $year)
                            <tr class="hover:bg-page-soft/80">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $year->name }}
                                    @if ($year->is_current)
                                        <span class="ml-2 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">{{ __('Current') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700 tabular-nums">{{ $year->starts_on?->toDateString() ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700 tabular-nums">{{ $year->ends_on?->toDateString() ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700 capitalize">{{ $year->status }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        @if (! $year->is_current)
                                            <form method="POST" action="{{ route('academic-years.set-current', $year) }}">
                                                @csrf
                                                <button type="submit" class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-800 hover:bg-emerald-100">{{ __('Set current') }}</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('academic-years.edit', $year) }}" class="rounded-md border border-secondary/60 bg-page px-3 py-1.5 text-xs font-medium text-secondary hover:bg-page-soft">{{ __('Edit') }}</a>
                                        @unless ($year->is_current)
                                            <form method="POST" action="{{ route('academic-years.destroy', $year) }}" onsubmit="return confirm({{ json_encode(__('Remove this academic year?')) }})">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">{{ __('Remove') }}</button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
