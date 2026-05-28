@extends('layouts.app')

@section('title', __('Terms'))

@section('header-title', __('Settings'))

@section('content')
    @include('settings._subnav')

    <h1 class="text-2xl font-semibold text-primary">{{ __('Terms') }}</h1>
    <p class="mt-1 text-sm text-gray-600">{{ __('Term 1, Term 2 and Term 3 inside each academic year. Only one term per tenant can be the current term.') }}</p>

    @if ($currentYear)
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">
            <strong>{{ __('Current academic year') }}:</strong> {{ $currentYear->name }}
            @if ($currentTerm)
                · <strong>{{ __('Current term') }}:</strong> {{ $currentTerm->name }}
            @else
                · {{ __('No current term set yet.') }}
            @endif
        </div>
    @else
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50/60 px-4 py-3 text-sm text-amber-900">
            {{ __('No current academic year. Create or set a current academic year before marking terms.') }}
            <a href="{{ route('academic-years.index') }}" class="ml-1 font-medium underline">{{ __('Go to academic years') }}</a>
        </div>
    @endif

    @if (session('error'))
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ session('error') }}</div>
    @endif

    <div class="mt-8 rounded-lg border border-gray-200 bg-page p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-secondary">{{ __('Add term') }}</h2>
        <form method="POST" action="{{ route('terms.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
            @csrf
            <div>
                <label for="new_academic_year_id" class="block text-sm font-medium text-gray-700">{{ __('Academic year') }} <span class="text-red-600">*</span></label>
                <select id="new_academic_year_id" name="academic_year_id" required
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('academic_year_id') border-red-500 @enderror">
                    <option value="">—</option>
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $currentYear?->id) === (string) $year->id)>{{ $year->name }}@if($year->is_current) ({{ __('current') }})@endif</option>
                    @endforeach
                </select>
                @error('academic_year_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="new_term_order" class="block text-sm font-medium text-gray-700">{{ __('Term number') }} <span class="text-red-600">*</span></label>
                <select id="new_term_order" name="term_order" required
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('term_order') border-red-500 @enderror">
                    @foreach ([1, 2, 3] as $n)
                        <option value="{{ $n }}" @selected((int) old('term_order') === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                @error('term_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="new_term_name" class="block text-sm font-medium text-gray-700">{{ __('Name') }} <span class="text-red-600">*</span></label>
                <input id="new_term_name" name="name" type="text" required value="{{ old('name', 'Term '.((int)(old('term_order') ?: 1))) }}" maxlength="128"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('name') border-red-500 @enderror">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="new_starts_on" class="block text-sm font-medium text-gray-700">{{ __('Starts on') }}</label>
                <input id="new_starts_on" name="starts_on" type="date" value="{{ old('starts_on') }}"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('starts_on') border-red-500 @enderror">
                @error('starts_on')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="new_ends_on" class="block text-sm font-medium text-gray-700">{{ __('Ends on') }}</label>
                <input id="new_ends_on" name="ends_on" type="date" value="{{ old('ends_on') }}"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('ends_on') border-red-500 @enderror">
                @error('ends_on')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2 flex items-center gap-2">
                <input id="new_is_current" name="is_current" type="checkbox" value="1" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                <label for="new_is_current" class="text-sm text-gray-700">{{ __('Mark as current term') }}</label>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Add term') }}</button>
            </div>
        </form>
    </div>

    <div class="mt-8 flex flex-wrap items-center gap-3">
        <form method="GET" action="{{ route('terms.index') }}" class="flex items-center gap-2">
            <label for="filter_academic_year_id" class="text-sm text-gray-700">{{ __('Filter by year') }}</label>
            <select id="filter_academic_year_id" name="academic_year_id" onchange="this.form.submit()"
                class="rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                <option value="">{{ __('All') }}</option>
                @foreach ($years as $year)
                    <option value="{{ $year->id }}" @selected((int) $selectedYearId === (int) $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 bg-page">
        @if ($terms->isEmpty())
            <div class="p-6">
                <x-empty-state :title="__('No terms in this view')" :message="__('Add Term 1, 2 and 3 for the selected academic year.')" />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-page-soft">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Term') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Academic year') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Starts') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Ends') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-page">
                        @foreach ($terms as $term)
                            <tr class="hover:bg-page-soft/80">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $term->name }}
                                    @if ($term->is_current)
                                        <span class="ml-2 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">{{ __('Current') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $term->academicYear?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700 tabular-nums">{{ $term->starts_on?->toDateString() ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700 tabular-nums">{{ $term->ends_on?->toDateString() ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        @if (! $term->is_current && $term->academicYear?->is_current)
                                            <form method="POST" action="{{ route('terms.set-current', $term) }}">
                                                @csrf
                                                <button type="submit" class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-800 hover:bg-emerald-100">{{ __('Set current') }}</button>
                                            </form>
                                        @endif
                                        <details class="relative">
                                            <summary class="cursor-pointer list-none rounded-md border border-secondary/60 bg-page px-3 py-1.5 text-xs font-medium text-secondary hover:bg-page-soft">{{ __('Edit') }}</summary>
                                            <div class="absolute right-0 z-10 mt-1 w-80 rounded-md border border-gray-200 bg-page p-3 text-left shadow-lg">
                                                <form method="POST" action="{{ route('terms.update', $term) }}" class="space-y-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="academic_year_id" value="{{ $term->academic_year_id }}">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700">{{ __('Name') }}</label>
                                                        <input type="text" name="name" value="{{ $term->name }}" required class="mt-1 w-full rounded-md border border-gray-300 px-2 py-1 text-sm">
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700">{{ __('No.') }}</label>
                                                            <input type="number" name="term_order" min="1" max="6" value="{{ $term->term_order }}" required class="mt-1 w-full rounded-md border border-gray-300 px-2 py-1 text-sm">
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700">{{ __('Starts') }}</label>
                                                            <input type="date" name="starts_on" value="{{ $term->starts_on?->toDateString() }}" class="mt-1 w-full rounded-md border border-gray-300 px-2 py-1 text-sm">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700">{{ __('Ends') }}</label>
                                                            <input type="date" name="ends_on" value="{{ $term->ends_on?->toDateString() }}" class="mt-1 w-full rounded-md border border-gray-300 px-2 py-1 text-sm">
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="w-full rounded-md bg-primary px-3 py-1.5 text-xs font-medium text-white hover:bg-primary/95">{{ __('Save') }}</button>
                                                </form>
                                            </div>
                                        </details>
                                        @unless ($term->is_current)
                                            <form method="POST" action="{{ route('terms.destroy', $term) }}" onsubmit="return confirm({{ json_encode(__('Delete this term?')) }})">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
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
