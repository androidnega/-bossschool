@extends('layouts.app')

@section('title', __('Platform settings'))

@section('header-title', __('Platform settings'))

@section('content')
    <div class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        <h1 class="text-xl font-semibold text-slate-900">{{ __('BossSchool platform settings') }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ __('Branding, billing defaults, access, and maintenance copy.') }}</p>

        @if (session('status'))
            <div class="mt-4 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">{{ session('status') }}</div>
        @endif

        @php
            $s = $settings ?? [];
        @endphp

        <form method="POST" action="{{ route('platform.settings.update') }}" class="mt-8 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="platform_name">{{ __('Platform name') }}</label>
                <input id="platform_name" name="platform_name" type="text" value="{{ old('platform_name', $s['platform_name'] ?? 'BossSchool') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('platform_name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="support_email">{{ __('Support email') }}</label>
                <input id="support_email" name="support_email" type="email" value="{{ old('support_email', $s['support_email'] ?? '') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('support_email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="support_phone">{{ __('Support phone') }}</label>
                <input id="support_phone" name="support_phone" type="text" value="{{ old('support_phone', $s['support_phone'] ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('support_phone')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="default_trial_days">{{ __('Default trial days') }}</label>
                <input id="default_trial_days" name="default_trial_days" type="number" min="1" max="365" value="{{ old('default_trial_days', $s['default_trial_days'] ?? 14) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('default_trial_days')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="default_currency">{{ __('Default currency') }}</label>
                <input id="default_currency" name="default_currency" type="text" value="{{ old('default_currency', $s['default_currency'] ?? 'GHS') }}" required maxlength="8" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('default_currency')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <input id="allow_school_registration" name="allow_school_registration" type="checkbox" value="1" class="size-4 rounded border-slate-300" @checked(old('allow_school_registration', ($s['allow_school_registration'] ?? '1') === '1' || ($s['allow_school_registration'] ?? '') === 'true'))>
                <label for="allow_school_registration" class="text-sm text-slate-800">{{ __('Allow school registration') }}</label>
            </div>
            <div class="flex items-center gap-3">
                <input id="require_subscription_payment" name="require_subscription_payment" type="checkbox" value="1" class="size-4 rounded border-slate-300" @checked(old('require_subscription_payment', ($s['require_subscription_payment'] ?? '0') === '1'))>
                <label for="require_subscription_payment" class="text-sm text-slate-800">{{ __('Require subscription payment') }}</label>
            </div>
            <div class="flex items-center gap-3">
                <input id="maintenance_enabled" name="maintenance_enabled" type="checkbox" value="1" class="size-4 rounded border-slate-300" @checked(old('maintenance_enabled', ($s['maintenance_enabled'] ?? '0') === '1'))>
                <label for="maintenance_enabled" class="text-sm text-slate-800">{{ __('Global maintenance enabled') }}</label>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="maintenance_message">{{ __('Maintenance message') }}</label>
                <textarea id="maintenance_message" name="maintenance_message" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('maintenance_message', $s['maintenance_message'] ?? '') }}</textarea>
                @error('maintenance_message')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">{{ __('Save settings') }}</button>
        </form>
    </div>
@endsection
