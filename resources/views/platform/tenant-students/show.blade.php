@extends('layouts.app')

@section('title', $student->name)

@section('header-title', __('Student profile'))

@section('content')
    @include('platform.tenants._control-nav', ['tenant' => $tenant])

    <a href="{{ route('platform.tenants.students.index', $tenant) }}" class="text-sm font-medium text-teal-800 hover:underline">{{ __('← Students') }}</a>

    <h1 class="mt-4 text-2xl font-semibold text-slate-900">{{ $student->name }}</h1>
    <p class="mt-1 text-sm text-slate-600">{{ __('Class') }}: {{ $student->schoolClass?->name ?? '—' }} · {{ __('Status') }}: {{ $student->status }}</p>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Parent / guardian') }}</h2>
            <p class="mt-2 text-slate-900">{{ $student->parent_name }}</p>
            <p class="text-sm text-slate-600">{{ $student->parent_phone }}</p>
            <p class="mt-2 text-sm text-slate-600">{{ $student->address }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-sky-50 p-5">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-sky-900">{{ __('Fee summary') }}</h2>
            <p class="mt-2 text-sm text-sky-950">{{ __('Expected') }}: {{ cedis($feeExpected) }}</p>
            <p class="text-sm text-sky-950">{{ __('Paid') }}: {{ cedis($feePaid) }}</p>
            <p class="text-sm font-semibold text-sky-950">{{ __('Balance') }}: {{ cedis($feeBalance) }}</p>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Linked accounts') }}</h2>
        <p class="mt-2 text-sm text-slate-700">{{ __('Student portal user') }}:
            @if ($student->linkedUser)
                {{ $student->linkedUser->name }} ({{ $student->linkedUser->email }})
            @else
                {{ __('None') }}
            @endif
        </p>
        <p class="mt-3 text-sm text-slate-700">{{ __('Parent users linked') }}:</p>
        <ul class="mt-1 list-inside list-disc text-sm text-slate-600">
            @forelse ($parentAccounts as $p)
                <li>{{ $p->name }} — {{ $p->email }}</li>
            @empty
                <li>{{ __('None') }}</li>
            @endforelse
        </ul>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">{{ __('Results rows') }}</p>
            <p class="text-lg font-semibold text-slate-900">{{ number_format($resultsCount) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">{{ __('Attendance rows') }}</p>
            <p class="text-lg font-semibold text-slate-900">{{ number_format($attendanceCount) }}</p>
        </div>
    </div>
@endsection
