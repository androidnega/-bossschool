@extends('layouts.app')

@section('title', __('Student report'))

@section('header-title', __('Reports'))

@section('content')
    @include('reports._subnav')

    <h1 class="text-2xl font-semibold text-primary">{{ __('Student report') }}</h1>
    <p class="mt-1 text-sm text-gray-600">{{ __('Enrolment summary and distribution.') }}</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-stone-200/80 bg-card-mist p-6 ring-1 ring-stone-200/40">
            <p class="text-sm font-medium text-stone-600">{{ __('Total students') }}</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums text-stone-900">{{ number_format($total) }}</p>
        </div>
        <div class="rounded-2xl border border-stone-200/80 bg-card-sage p-6 ring-1 ring-stone-200/40">
            <p class="text-sm font-medium text-stone-600">{{ __('Active') }}</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums text-stone-900">{{ number_format($active) }}</p>
        </div>
        <div class="rounded-2xl border border-stone-200/80 bg-card-sand p-6 ring-1 ring-stone-200/40">
            <p class="text-sm font-medium text-stone-600">{{ __('Non-active') }}</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums text-stone-900">{{ number_format($inactive) }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-page p-6">
            <h2 class="text-lg font-semibold text-primary">{{ __('Students per class') }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($perClass as $row)
                    @php
                        $pct = $total > 0 ? round(($row['count'] / $total) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-800">{{ $row['class'] ? $row['class']->name.($row['class']->section ? ' — '.$row['class']->section : '') : __('Unknown class') }}</span>
                            <span class="tabular-nums font-medium text-gray-900">{{ $row['count'] }}</span>
                        </div>
                        <div class="mt-1 h-2 overflow-hidden rounded bg-stone-200">
                            <div class="h-full bg-stone-500" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-600">{{ __('No data.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-page p-6">
            <h2 class="text-lg font-semibold text-primary">{{ __('Gender distribution') }}</h2>
            <div class="mt-4 space-y-3">
                @foreach ($genderRows as $row)
                    @php
                        $pct = $genderMax > 0 ? round(((int) $row->c / $genderMax) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="capitalize text-gray-800">{{ str_replace('_', ' ', (string) $row->g) }}</span>
                            <span class="tabular-nums font-medium text-gray-900">{{ $row->c }}</span>
                        </div>
                        <div class="mt-1 h-2 overflow-hidden rounded bg-page-soft">
                            <div class="h-full bg-accent" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
