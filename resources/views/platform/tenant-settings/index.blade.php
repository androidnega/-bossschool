@extends('layouts.app')

@section('title', __('Settings'))

@section('header-title', __('Tenant settings'))

@section('content')
    @include('platform.tenants._control-nav', ['tenant' => $tenant])

    <h1 class="text-xl font-semibold text-slate-900">{{ __('School & tenant settings') }} — {{ $tenant->name }}</h1>

    @if (session('status'))
        <div class="mt-4 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">{{ session('status') }}</div>
    @endif

    @php $school = $tenant->school; @endphp

    <form method="POST" action="{{ route('platform.tenants.settings.update', $tenant) }}" class="mt-6 max-w-2xl space-y-5 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')

        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Tenant') }}</h2>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="tenant_name">{{ __('Tenant / legal name') }}</label>
            <input id="tenant_name" name="tenant_name" value="{{ old('tenant_name', $tenant->name) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('tenant_name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="subdomain">{{ __('Subdomain') }}</label>
            <input id="subdomain" name="subdomain" value="{{ old('subdomain', $tenant->subdomain) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono">
            @error('subdomain')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="tenant_status">{{ __('Tenant status') }}</label>
            <select id="tenant_status" name="tenant_status" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ([\App\Models\Tenant::STATUS_ACTIVE, \App\Models\Tenant::STATUS_TRIAL, \App\Models\Tenant::STATUS_SUSPENDED] as $st)
                    <option value="{{ $st }}" @selected(old('tenant_status', $tenant->status) === $st)>{{ $st }}</option>
                @endforeach
            </select>
            @error('tenant_status')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="trial_end">{{ __('Trial end (trial only)') }}</label>
            <input id="trial_end" name="trial_end" type="date" value="{{ old('trial_end', $tenant->trial_end?->toDateString()) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('trial_end')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <h2 class="border-t border-slate-200 pt-5 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('School profile') }}</h2>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="school_name">{{ __('School display name') }}</label>
            <input id="school_name" name="school_name" value="{{ old('school_name', $school?->name ?? $tenant->name) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('school_name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="address">{{ __('Address') }}</label>
            <textarea id="address" name="address" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('address', $school?->address) }}</textarea>
            @error('address')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="phone">{{ __('Phone') }}</label>
                <input id="phone" name="phone" value="{{ old('phone', $school?->phone) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('phone')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="email">{{ __('School email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email', $school?->email) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700" for="academic_year">{{ __('Academic year') }}</label>
            <input id="academic_year" name="academic_year" value="{{ old('academic_year', $school?->academic_year) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('academic_year')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">{{ __('Save settings') }}</button>
    </form>
@endsection
