@extends('layouts.app')

@section('title', __('Plans'))

@section('header-title', __('Billing'))

@section('content')
    @include('billing._subnav')

    <h1 class="text-2xl font-semibold text-primary">{{ __('Plans') }}</h1>
    <p class="mt-1 text-sm text-gray-600">{{ __('Starter, Growth, Standard, and Premium.') }}</p>

    <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($plans as $plan)
            @php
                $isCurrent = $tenant->plan_id === $plan->id;
            @endphp
            <div class="flex flex-col rounded-lg border {{ $isCurrent ? 'border-primary' : 'border-gray-200' }} bg-page p-6">
                <h2 class="text-lg font-semibold text-primary">{{ $plan->name }}</h2>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-gray-900">{{ cedis($plan->price) }} <span class="text-sm font-normal text-gray-500">/ {{ __('yr') }}</span></p>
                <dl class="mt-4 space-y-1 text-sm text-gray-700">
                    <div class="flex justify-between gap-2">
                        <dt>{{ __('Students') }}</dt>
                        <dd class="font-medium tabular-nums">{{ number_format($plan->max_students) }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt>{{ __('Staff') }}</dt>
                        <dd class="font-medium tabular-nums">{{ number_format($plan->max_staff) }}</dd>
                    </div>
                </dl>
                @if (is_array($plan->features) && count($plan->features) > 0)
                    <ul class="mt-4 flex-1 list-inside list-disc space-y-1 text-sm text-gray-600">
                        @foreach ($plan->features as $feature)
                            <li>{{ str_replace('_', ' ', (string) $feature) }}</li>
                        @endforeach
                    </ul>
                @endif
                @if ($isCurrent)
                    <p class="mt-4 text-sm font-medium text-secondary">{{ __('Current plan') }}</p>
                @endif
                @can('billing.manage')
                    @if (! $isCurrent)
                        <div class="mt-6 space-y-2">
                            @if ($paystackEnabled && (float) $plan->price > 0)
                                <form method="POST" action="{{ route('billing.subscribe.paystack', $plan) }}" onsubmit="return confirm({{ json_encode(__('Continue to Paystack to pay :amount?', ['amount' => cedis($plan->price)])) }})">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-md bg-emerald-600 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                                        <i class="fa-solid fa-credit-card" aria-hidden="true"></i>
                                        {{ __('Pay with Paystack') }}
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('billing.subscribe', $plan) }}" onsubmit="return confirm({{ json_encode(__('Switch to this plan without taking a payment?')) }})">
                                @csrf
                                <button type="submit" class="w-full rounded-md border border-primary bg-white py-2 text-sm font-medium text-primary hover:bg-primary/5">{{ __('Activate without payment') }}</button>
                            </form>
                        </div>
                    @endif
                @else
                    <p class="mt-6 text-xs text-gray-500">{{ __('Only administrators can change plans.') }}</p>
                @endcan
            </div>
        @endforeach
    </div>
@endsection
