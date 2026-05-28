@extends('layouts.app')

@section('title', __('Billing'))

@section('header-title', __('Billing'))

@section('content')
    @include('billing._subnav')

    <h1 class="text-2xl font-semibold text-primary">{{ __('Billing overview') }}</h1>
    <p class="mt-1 text-sm text-gray-600">{{ __('Current subscription and trial status.') }}</p>

    @php
        $currentPlan = $activeSubscription?->plan ?? $tenant->plan;
    @endphp

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-page p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-secondary">{{ __('Current plan') }}</h2>
            @if ($currentPlan)
                <p class="mt-3 text-2xl font-semibold text-primary">{{ $currentPlan->name }}</p>
                <p class="mt-1 text-sm text-gray-600">{{ cedis($currentPlan->price) }} <span class="text-gray-500">/ {{ __('year') }}</span></p>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">{{ __('Max students') }}</dt>
                        <dd class="font-medium text-gray-900">{{ number_format($currentPlan->max_students) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">{{ __('Max staff') }}</dt>
                        <dd class="font-medium text-gray-900">{{ number_format($currentPlan->max_staff) }}</dd>
                    </div>
                </dl>
            @else
                <p class="mt-3 text-gray-600">{{ __('No plan assigned.') }}</p>
            @endif
        </div>

        <div class="rounded-lg border border-gray-200 bg-page p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-secondary">{{ __('Subscription') }}</h2>
            @if ($activeSubscription)
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">{{ __('Status') }}</dt>
                        <dd class="font-medium capitalize text-gray-900">{{ str_replace('_', ' ', $activeSubscription->status) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">{{ __('Start') }}</dt>
                        <dd class="text-gray-900">{{ $activeSubscription->start_date?->format('M j, Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">{{ __('End') }}</dt>
                        <dd class="text-gray-900">{{ $activeSubscription->end_date?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="mt-4 text-sm text-gray-600">{{ __('No active subscription record. Compare plans to subscribe.') }}</p>
            @endif

            <div class="mt-6 border-t border-gray-100 pt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-secondary">{{ __('Trial') }}</p>
                <p class="mt-1 text-sm text-gray-900">
                    @if ($tenant->trial_end)
                        {{ $tenant->trial_end->format('M j, Y g:i A') }}
                    @else
                        {{ __('Not set') }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50/60 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-emerald-700">{{ __('SMS credits') }}</h2>
                <p class="mt-2 text-3xl font-semibold tabular-nums text-emerald-900">{{ number_format((int) ($tenant->sms_credits_balance ?? 0)) }}</p>
                <p class="mt-1 text-xs text-emerald-700">{{ __('messages remaining') }}</p>
            </div>
            <a href="{{ route('billing.sms-credits.index') }}" class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                {{ __('Top up SMS credits') }}
            </a>
        </div>
    </div>

    <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ route('billing.plans') }}" class="rounded-md border border-secondary/60 bg-page px-4 py-2 text-sm font-medium text-secondary hover:bg-page-soft">{{ __('Compare plans') }}</a>
        <a href="{{ route('billing.history') }}" class="rounded-md border border-gray-300 bg-page px-4 py-2 text-sm font-medium text-gray-700 hover:bg-page-soft">{{ __('Subscription history') }}</a>
    </div>
@endsection
