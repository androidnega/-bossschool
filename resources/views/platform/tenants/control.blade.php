@extends('layouts.app')

@section('title', $tenant->name)

@section('header-title', __('Tenant control'))

@section('content')
    @include('platform.tenants._control-nav', ['tenant' => $tenant])

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $tenant->school?->name ?? $tenant->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">
                <span class="font-mono">{{ $tenant->subdomain }}</span>
                · {{ __('Status') }}: <span class="font-medium">{{ $tenant->status }}</span>
                @if ($tenant->plan)
                    · {{ __('Plan') }}: {{ $tenant->plan->name }}
                @endif
            </p>
            @if ($tenant->trial_end)
                <p class="mt-1 text-sm text-slate-600">{{ __('Trial ends') }}: {{ $tenant->trial_end->toFormattedDateString() }}</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($tenant->status !== \App\Models\Tenant::STATUS_SUSPENDED)
                <form method="POST" action="{{ route('platform.tenants.suspend', $tenant) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-rose-300 bg-white px-4 py-2 text-sm font-medium text-rose-800 hover:bg-rose-50">
                        <i class="fa-solid fa-pause" aria-hidden="true"></i>{{ __('Suspend school') }}
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('platform.tenants.activate', $tenant) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-white px-4 py-2 text-sm font-medium text-emerald-800 hover:bg-emerald-50">
                        <i class="fa-solid fa-play" aria-hidden="true"></i>{{ __('Activate school') }}
                    </button>
                </form>
            @endif
            <a href="{{ route('platform.reset.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-900 hover:bg-amber-100">
                <i class="fa-solid fa-rotate" aria-hidden="true"></i>{{ __('Reset tools') }}
            </a>
            <a href="{{ route('platform.tenants.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">{{ __('All schools') }}</a>
        </div>
    </div>

    <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50/80 p-5">
        <h2 class="text-sm font-semibold text-amber-950">{{ __('Tenant maintenance') }}</h2>
        <p class="mt-1 text-sm text-amber-900">{{ __('Only users of this school see the maintenance page when enabled.') }}</p>
        <p class="mt-2 text-xs text-amber-800">{{ __('Status') }}: {{ ($tenantMaintenance?->is_enabled ?? false) ? __('On') : __('Off') }}</p>
        @if ($tenantMaintenance?->message)
            <p class="mt-1 text-sm text-amber-900">{{ $tenantMaintenance->message }}</p>
        @endif
        <div class="mt-4 flex flex-wrap gap-2">
            <form method="POST" action="{{ route('platform.tenants.maintenance.enable', $tenant) }}" class="flex flex-wrap items-end gap-2">
                @csrf
                <input type="text" name="message" value="{{ old('message', $tenantMaintenance?->message) }}" placeholder="{{ __('Optional message') }}" class="min-w-[12rem] flex-1 rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm">
                <button type="submit" class="rounded-lg bg-amber-700 px-4 py-2 text-sm font-medium text-white hover:bg-amber-800">{{ __('Enable for tenant') }}</button>
            </form>
            <form method="POST" action="{{ route('platform.tenants.maintenance.disable', $tenant) }}" class="inline" onsubmit="return confirm(@json(__('Disable tenant maintenance?')));">
                @csrf
                <button type="submit" class="rounded-lg border border-amber-400 bg-white px-4 py-2 text-sm font-medium text-amber-950 hover:bg-amber-100">{{ __('Disable tenant maintenance') }}</button>
            </form>
        </div>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900" role="status">{{ session('status') }}</div>
    @endif

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Users') }}</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($summary['user_count']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-sky-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-sky-800">{{ __('Students') }}</p>
            <p class="mt-1 text-2xl font-semibold text-sky-950">{{ number_format($summary['student_count']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-amber-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-amber-900">{{ __('Staff') }}</p>
            <p class="mt-1 text-2xl font-semibold text-amber-950">{{ number_format($summary['staff_count']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-violet-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-violet-900">{{ __('Classes') }}</p>
            <p class="mt-1 text-2xl font-semibold text-violet-950">{{ number_format($summary['class_count']) }}</p>
        </div>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-emerald-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-emerald-900">{{ __('Payments total') }}</p>
            <p class="mt-1 text-xl font-semibold text-emerald-950">{{ cedis($summary['payments_total']) }}</p>
            <p class="mt-1 text-xs text-emerald-800">{{ __(':count records', ['count' => $summary['payments_count']]) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-rose-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-rose-900">{{ __('Outstanding fees (est.)') }}</p>
            <p class="mt-1 text-xl font-semibold text-rose-950">{{ cedis($summary['outstanding_fees_estimate']) }}</p>
            <p class="mt-1 text-xs text-rose-800">{{ __('Debtors (active students)') }}: {{ number_format($debtors['count']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Subscription') }}</p>
            @if ($activeSubscription)
                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $activeSubscription->plan?->name ?? '—' }}</p>
                <p class="mt-1 text-xs text-slate-600">{{ __('Status') }}: {{ $activeSubscription->status }}</p>
                @if ($activeSubscription->end_date)
                    <p class="text-xs text-slate-600">{{ __('Ends') }} {{ $activeSubscription->end_date->toFormattedDateString() }}</p>
                @endif
            @else
                <p class="mt-1 text-sm text-slate-600">{{ __('No subscription row.') }}</p>
            @endif
        </div>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">{{ __('Results rows') }}</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ number_format($summary['results_count']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">{{ __('Attendance rows') }}</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ number_format($summary['attendance_count']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">{{ __('School messages') }}</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ number_format($summary['messages_count']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">{{ __('Last activity') }}</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $summary['last_activity_at']?->diffForHumans() ?? __('Unknown') }}</p>
        </div>
    </div>

    <div class="mt-10">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Quick actions') }}</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('platform.tenants.users.index', $tenant) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-users" aria-hidden="true"></i>{{ __('View users') }}</a>
            <a href="{{ route('platform.tenants.students.index', $tenant) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-user-graduate" aria-hidden="true"></i>{{ __('View students') }}</a>
            <a href="{{ route('platform.tenants.staff.index', $tenant) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-id-badge" aria-hidden="true"></i>{{ __('View staff') }}</a>
            <a href="{{ route('platform.tenants.finance.index', $tenant) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-coins" aria-hidden="true"></i>{{ __('View finance') }}</a>
            <a href="{{ route('platform.tenants.academics.index', $tenant) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-book" aria-hidden="true"></i>{{ __('View academics') }}</a>
            <a href="{{ route('platform.tenants.attendance.index', $tenant) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>{{ __('View attendance') }}</a>
            <a href="{{ route('platform.tenants.messages.index', $tenant) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i>{{ __('View messages') }}</a>
            <a href="{{ route('platform.tenants.subscription.index', $tenant) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-file-contract" aria-hidden="true"></i>{{ __('View subscription') }}</a>
            <a href="{{ route('platform.tenants.settings.index', $tenant) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-gear" aria-hidden="true"></i>{{ __('Edit school settings') }}</a>
        </div>
    </div>

    <div class="mt-10 max-w-2xl rounded-xl border border-rose-200 bg-rose-50/60 p-6">
        <h2 class="text-lg font-semibold text-rose-950">{{ __('Danger zone') }}</h2>
        <p class="mt-1 text-sm text-rose-900">{{ __('Soft-delete this tenant. Users will lose access. Type the confirmation phrase exactly.') }}</p>
        <form method="POST" action="{{ route('platform.tenants.destroy', $tenant) }}" class="mt-4 space-y-3" onsubmit="return confirm(@json(__('Permanently remove this school from the platform directory?')));">
            @csrf
            @method('DELETE')
            <div>
                <label class="mb-1 block text-sm font-medium text-rose-900" for="confirm">{{ __('Type DELETE TENANT to confirm') }}</label>
                <input id="confirm" name="confirm" type="text" autocomplete="off" class="mt-1 w-full rounded-lg border border-rose-300 bg-white px-3 py-2 text-sm" placeholder="DELETE TENANT" required>
            </div>
            <button type="submit" class="rounded-lg bg-rose-700 px-4 py-2 text-sm font-medium text-white hover:bg-rose-800">{{ __('Delete tenant') }}</button>
        </form>
    </div>
@endsection
