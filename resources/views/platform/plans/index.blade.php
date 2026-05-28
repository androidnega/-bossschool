@extends('layouts.app')

@section('title', __('Plans'))

@section('header-title', __('Subscription plans'))

@section('content')
    <div class="flex flex-col gap-3 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Plans') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('Pricing and limits for BossSchool tenants. Prefer disabling a plan instead of deleting when schools still use it.') }}</p>
        </div>
        <a href="{{ route('platform.plans.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>{{ __('Add plan') }}
        </a>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">{{ session('status') }}</div>
    @endif
    @error('plan')
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">{{ $message }}</div>
    @enderror

    <div class="mt-8 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Price') }}</th>
                    <th class="px-4 py-3">{{ __('Billing') }}</th>
                    <th class="px-4 py-3">{{ __('Limits') }}</th>
                    <th class="px-4 py-3">{{ __('Active') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($plans as $plan)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $plan->name }}</td>
                        <td class="px-4 py-3 text-slate-800">{{ cedis((float) $plan->price) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $plan->billing_cycle }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ __(':s students · :t staff', ['s' => $plan->max_students, 't' => $plan->max_staff]) }}</td>
                        <td class="px-4 py-3">
                            @if ($plan->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">{{ __('Yes') }}</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ __('No') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('platform.plans.edit', $plan) }}" class="text-teal-800 hover:underline">{{ __('Edit') }}</a>
                            @if ($plan->is_active)
                                <form method="POST" action="{{ route('platform.plans.disable', $plan) }}" class="ms-3 inline" onsubmit="return confirm(@json(__('Disable this plan for new subscriptions?')));">
                                    @csrf
                                    <button type="submit" class="text-amber-800 hover:underline">{{ __('Disable') }}</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('platform.plans.destroy', $plan) }}" class="ms-3 inline" onsubmit="return confirm(@json(__('Delete this plan permanently?')));">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-800 hover:underline">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">{{ __('No plans yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
