@extends('layouts.app')

@section('title', __('Subscription'))

@section('header-title', __('Tenant subscription'))

@section('content')
    @include('platform.tenants._control-nav', ['tenant' => $tenant])

    <h1 class="text-xl font-semibold text-slate-900">{{ __('Subscription') }} — {{ $tenant->name }}</h1>

    @if (session('status'))
        <div class="mt-4 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">{{ session('status') }}</div>
    @endif
    @error('extend')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
    @error('activate')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('Current record') }}</h2>
        @if ($current)
            <p class="mt-2 text-sm text-slate-700">{{ __('Plan') }}: {{ $current->plan?->name }}</p>
            <p class="text-sm text-slate-700">{{ __('Status') }}: {{ $current->status }}</p>
            <p class="text-sm text-slate-700">{{ __('Amount') }}: {{ $current->amount !== null ? cedis((float) $current->amount) : '—' }}</p>
            <p class="text-sm text-slate-700">{{ __('Billing') }}: {{ $current->billing_cycle ?? '—' }}</p>
            <p class="text-sm text-slate-700">{{ __('Start') }}: {{ $current->start_date?->toDateString() }}</p>
            <p class="text-sm text-slate-700">{{ __('End') }}: {{ $current->end_date?->toDateString() ?? '—' }}</p>
            @if ($current->note)
                <p class="mt-2 text-sm text-slate-600">{{ __('Note') }}: {{ $current->note }}</p>
            @endif
        @else
            <p class="mt-2 text-sm text-slate-600">{{ __('No subscription history yet.') }}</p>
        @endif
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        <form method="POST" action="{{ route('platform.tenants.subscription.extend', $tenant) }}" class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white p-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600" for="days">{{ __('Extend (days)') }}</label>
                <input id="days" name="days" type="number" min="1" max="366" value="30" required class="w-24 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-900">{{ __('Extend') }}</button>
        </form>
        <form method="POST" action="{{ route('platform.tenants.subscription.suspend', $tenant) }}" class="inline" onsubmit="return confirm(@json(__('Suspend (cancel) active subscription?')));">
            @csrf
            <button type="submit" class="rounded-lg border border-rose-300 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-900 hover:bg-rose-100">{{ __('Suspend subscription') }}</button>
        </form>
        <form method="POST" action="{{ route('platform.tenants.subscription.activate', $tenant) }}" class="inline">
            @csrf
            <button type="submit" class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-900 hover:bg-emerald-100">{{ __('Activate subscription') }}</button>
        </form>
    </div>

    <form method="POST" action="{{ route('platform.tenants.subscription.update', $tenant) }}" class="mt-8 max-w-lg space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')
        <p class="text-sm font-medium text-slate-800">{{ __('Replace subscription row') }}</p>
        <p class="text-xs text-slate-500">{{ __('Saving expires the current active row and creates a new subscription line.') }}</p>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="plan_id">{{ __('Plan') }}</label>
            <select id="plan_id" name="plan_id" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ($plans as $p)
                    <option value="{{ $p->id }}" @selected((int) old('plan_id', $tenant->plan_id) === (int) $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            @error('plan_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="amount">{{ __('Amount (override)') }}</label>
            <input id="amount" name="amount" type="number" step="0.01" min="0" value="{{ old('amount', $current?->amount) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="{{ __('Leave blank to use plan price') }}">
            @error('amount')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="billing_cycle">{{ __('Billing cycle') }}</label>
            <select id="billing_cycle" name="billing_cycle" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">{{ __('Use plan default') }}</option>
                <option value="monthly" @selected(old('billing_cycle', $current?->billing_cycle) === 'monthly')>{{ __('Monthly') }}</option>
                <option value="yearly" @selected(old('billing_cycle', $current?->billing_cycle) === 'yearly')>{{ __('Yearly') }}</option>
            </select>
            @error('billing_cycle')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="start_date">{{ __('Start date') }}</label>
            <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $current?->start_date?->toDateString() ?? now()->toDateString()) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('start_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="end_date">{{ __('End date') }}</label>
            <input id="end_date" name="end_date" type="date" value="{{ old('end_date', $current?->end_date?->toDateString()) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('end_date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="status">{{ __('Status') }}</label>
            <select id="status" name="status" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach (['active' => \App\Models\Subscription::STATUS_ACTIVE, 'cancelled' => \App\Models\Subscription::STATUS_CANCELLED, 'expired' => \App\Models\Subscription::STATUS_EXPIRED, 'past_due' => \App\Models\Subscription::STATUS_PAST_DUE] as $label => $val)
                    <option value="{{ $val }}" @selected(old('status', $current?->status ?? \App\Models\Subscription::STATUS_ACTIVE) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="note">{{ __('Internal note') }}</label>
            <textarea id="note" name="note" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('note', $current?->note) }}</textarea>
            @error('note')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">{{ __('Save subscription') }}</button>
    </form>

    <div class="mt-8">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('Recent history') }}</h2>
        <ul class="mt-2 space-y-1 text-sm text-slate-600">
            @foreach ($tenant->subscriptions as $s)
                <li>{{ $s->start_date?->toDateString() }} → {{ $s->end_date?->toDateString() ?? '—' }} · {{ $s->plan?->name }} · {{ $s->status }}@if ($s->amount !== null) · {{ cedis((float) $s->amount) }}@endif</li>
            @endforeach
        </ul>
    </div>
@endsection
