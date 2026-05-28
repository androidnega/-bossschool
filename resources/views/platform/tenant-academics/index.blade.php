@extends('layouts.app')

@section('title', __('Academics'))

@section('header-title', __('Tenant academics'))

@section('content')
    @include('platform.tenants._control-nav', ['tenant' => $tenant])

    <h1 class="text-xl font-semibold text-slate-900">{{ __('Academic overview') }} — {{ $tenant->name }}</h1>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">{{ __('Classes') }}</p>
            <p class="text-xl font-semibold text-slate-900">{{ $classes->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-sky-50 p-4">
            <p class="text-xs text-sky-900">{{ __('Subject rows') }}</p>
            <p class="text-xl font-semibold text-sky-950">{{ number_format($subjectsCount) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-violet-50 p-4">
            <p class="text-xs text-violet-900">{{ __('Result rows') }}</p>
            <p class="text-xl font-semibold text-violet-950">{{ number_format($resultsCount) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-amber-50 p-4">
            <p class="text-xs text-amber-900">{{ __('Report readiness') }}</p>
            <p class="text-xl font-semibold text-amber-950">{{ $readinessPct }}%</p>
            <p class="text-xs text-amber-800">{{ __('Active students without totals') }}: {{ $studentsWithoutResults }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('Classes') }}</h2>
            <ul class="mt-3 space-y-1 text-sm text-slate-700">
                @foreach ($classes as $c)
                    <li>{{ $c->name }} @if($c->section) ({{ $c->section }}) @endif — {{ __('Subjects') }}: {{ $subjectsByClass->get($c->id, 0) }}</li>
                @endforeach
            </ul>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('Average total score by subject') }}</h2>
            <ul class="mt-3 space-y-1 text-sm text-slate-700">
                @forelse ($averages as $row)
                    <li>{{ $row->subject_name }}: {{ number_format($row->avg_total, 1) }} (n={{ $row->count }})</li>
                @empty
                    <li class="text-slate-500">{{ __('No scored results') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
