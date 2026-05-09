@extends('layouts.app')

@section('title', __('Platform dashboard'))

@section('header-title', __('Platform'))

@section('content')
    @php
        $quickLinks = [
            [
                'href' => route('platform.tenants.create'),
                'icon' => 'fa-solid fa-plus',
                'label' => __('Add school'),
                'style' => 'primary',
            ],
            [
                'href' => route('platform.plans.index'),
                'icon' => 'fa-solid fa-layer-group',
                'label' => __('Manage plans'),
                'style' => 'default',
            ],
            [
                'href' => route('platform.subscriptions'),
                'icon' => 'fa-solid fa-file-contract',
                'label' => __('Subscriptions'),
                'style' => 'default',
            ],
            [
                'href' => route('platform.feature-toggles.index'),
                'icon' => 'fa-solid fa-toggle-on',
                'label' => __('Feature toggles'),
                'style' => 'default',
            ],
            [
                'href' => route('platform.maintenance.index'),
                'icon' => 'fa-solid fa-road-barrier',
                'label' => __('Maintenance'),
                'style' => 'default',
            ],
            [
                'href' => route('platform.activity-logs.index'),
                'icon' => 'fa-solid fa-clock-rotate-left',
                'label' => __('Activity logs'),
                'style' => 'default',
            ],
            [
                'href' => route('platform.settings.index'),
                'icon' => 'fa-solid fa-sliders',
                'label' => __('Platform settings'),
                'style' => 'default',
            ],
            [
                'href' => route('platform.reset.index'),
                'icon' => 'fa-solid fa-rotate-left',
                'label' => __('Reset tools'),
                'style' => 'danger',
            ],
        ];
    @endphp

    <div class="mx-auto max-w-6xl space-y-10 pb-4">
        <header class="space-y-3 border-b border-stone-200/80 pb-8">
            <p class="text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-primary">{{ __('Platform') }}</p>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0 space-y-2">
                    <h1 class="text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">{{ __('Platform dashboard') }}</h1>
                    <p class="max-w-xl text-sm leading-relaxed text-stone-600 sm:text-[15px]">
                        {{ __('Tenants, subscriptions, and platform health — not school day-to-day data.') }}
                    </p>
                </div>
            </div>
        </header>

        <section class="space-y-3" aria-labelledby="platform-quick-actions">
            <h2 id="platform-quick-actions" class="text-[0.7rem] font-semibold uppercase tracking-[0.12em] text-stone-500">{{ __('Quick actions') }}</h2>
            <ul class="grid grid-cols-2 gap-2.5 sm:grid-cols-4 sm:gap-3">
                @foreach ($quickLinks as $link)
                    @php
                        $base = 'group relative flex min-h-[4.25rem] flex-col justify-center gap-1 rounded-2xl border px-3.5 py-3 text-left transition sm:min-h-0 sm:px-4 sm:py-3.5';
                        $classes = match ($link['style']) {
                            'primary' => $base.' border-primary/25 bg-primary text-white shadow-sm hover:border-primary hover:bg-primary/95',
                            'danger' => $base.' border-rose-200/70 bg-rose-50/60 text-rose-950 hover:border-rose-300 hover:bg-rose-50',
                            default => $base.' border-stone-200/90 bg-stone-50/50 text-stone-800 hover:border-primary/20 hover:bg-white',
                        };
                        $iconClass = match ($link['style']) {
                            'primary' => 'text-white/90',
                            'danger' => 'text-rose-600',
                            default => 'text-primary',
                        };
                    @endphp
                    <li>
                        <a href="{{ $link['href'] }}" class="{{ $classes }}">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $link['style'] === 'primary' ? 'bg-white/15' : ($link['style'] === 'danger' ? 'bg-white/80' : 'bg-white ring-1 ring-stone-200/80') }}">
                                <i class="{{ $link['icon'] }} {{ $iconClass }} text-sm" aria-hidden="true"></i>
                            </span>
                            <span class="text-xs font-semibold leading-snug sm:text-[13px]">{{ $link['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="space-y-4" aria-labelledby="platform-metrics">
            <h2 id="platform-metrics" class="text-[0.7rem] font-semibold uppercase tracking-[0.12em] text-stone-500">{{ __('Overview') }}</h2>
            <div class="rounded-2xl border border-stone-200/80 bg-stone-50/40 p-4 sm:p-5">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <x-dash-card icon="fa-solid fa-school" :label="__('Total schools')" :value="number_format($tenantCount)" variant="platform" class="!bg-white/90" />
                    <x-dash-card icon="fa-solid fa-circle-check" :label="__('Active schools')" :value="number_format($activeSchools)" variant="revenue" class="!bg-white/90" />
                    <x-dash-card icon="fa-solid fa-ban" :label="__('Suspended')" :value="number_format($suspendedSchools)" variant="danger" class="!bg-white/90" />
                    <x-dash-card icon="fa-solid fa-hourglass-half" :label="__('Trial schools')" :value="number_format($trialSchools)" variant="debtors" class="!bg-white/90" />
                    <x-dash-card icon="fa-solid fa-road-barrier" :label="__('Maintenance')" :value="$maintenanceLabel" variant="neutral" class="!bg-white/90 sm:col-span-2 lg:col-span-1" />
                </div>
                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <x-dash-card icon="fa-solid fa-file-signature" :label="__('Active subscriptions')" :value="number_format($activeSubscriptions)" variant="results" class="!bg-white/90" />
                    <x-dash-card icon="fa-solid fa-calendar-xmark" :label="__('Expired subscriptions')" :value="number_format($expiredSubscriptions)" variant="debtors" class="!bg-white/90" />
                    <x-dash-card icon="fa-solid fa-users" :label="__('Total platform users')" :value="number_format($platformUserCount)" variant="staff" class="!bg-white/90" />
                    <x-dash-card icon="fa-solid fa-user-group" :label="__('Tenant users')" :value="number_format($tenantUsers)" variant="students" class="!bg-white/90" />
                    <x-dash-card icon="fa-solid fa-list" :label="__('Plans count')" :value="number_format($planCount)" variant="neutral" class="!bg-white/90 sm:col-span-2 lg:col-span-1" />
                </div>
            </div>
        </section>

        <div class="grid gap-8 lg:grid-cols-2">
            <section class="space-y-3">
                <h2 class="text-sm font-semibold text-stone-900">{{ __('Recent tenant registrations') }}</h2>
                <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-stone-100 bg-stone-50/80 text-[0.65rem] font-semibold uppercase tracking-wide text-stone-500">
                            <tr>
                                <th class="px-4 py-3">{{ __('School') }}</th>
                                <th class="px-4 py-3">{{ __('Status') }}</th>
                                <th class="px-4 py-3">{{ __('Created') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($recentTenants as $t)
                                <tr class="transition hover:bg-stone-50/60">
                                    <td class="px-4 py-3">
                                        <a class="font-medium text-primary hover:underline" href="{{ route('platform.tenants.show', $t) }}">{{ $t->name }}</a>
                                    </td>
                                    <td class="px-4 py-3 text-stone-600">{{ $t->status }}</td>
                                    <td class="px-4 py-3 text-stone-500">{{ $t->created_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-10 text-center text-sm text-stone-500">{{ __('No tenants yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="space-y-3">
                <h2 class="text-sm font-semibold text-stone-900">{{ __('Recent subscription changes') }}</h2>
                <ul class="divide-y divide-stone-100 overflow-hidden rounded-2xl border border-stone-200/80 bg-white text-sm">
                    @forelse ($recentSubscriptionChanges as $s)
                        <li class="flex flex-col gap-0.5 px-4 py-3.5 transition hover:bg-stone-50/60 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-2">
                            <span class="font-medium text-stone-900">{{ $s->tenant?->name ?? '—' }}</span>
                            <span class="text-stone-600">{{ $s->plan?->name }} · {{ $s->status }}</span>
                            <span class="text-xs text-stone-400 sm:shrink-0">{{ $s->updated_at?->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="px-4 py-10 text-center text-sm text-stone-500">{{ __('No subscription rows yet.') }}</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <div class="grid gap-8 lg:grid-cols-2">
            <section class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-stone-900">{{ __('Recent activity logs') }}</h2>
                    <a href="{{ route('platform.activity-logs.index') }}" class="text-xs font-medium text-primary hover:underline">{{ __('View all') }}</a>
                </div>
                <ul class="divide-y divide-stone-100 overflow-hidden rounded-2xl border border-stone-200/80 bg-white text-sm">
                    @forelse ($recentLogs as $log)
                        <li class="px-4 py-3.5 transition hover:bg-stone-50/60">
                            <p class="font-medium text-stone-900">{{ $log->action }}</p>
                            <p class="text-xs text-stone-500">{{ $log->actor_name ?? __('System') }} · {{ $log->created_at?->diffForHumans() }}</p>
                            @if ($log->description)
                                <p class="mt-1.5 leading-snug text-stone-600">{{ \Illuminate\Support\Str::limit($log->description, 120) }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="px-4 py-10 text-center text-sm text-stone-500">{{ __('No activity logged yet.') }}</li>
                    @endforelse
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-sm font-semibold text-stone-900">{{ __('Tenants needing attention') }}</h2>
                <ul class="divide-y divide-stone-100 overflow-hidden rounded-2xl border border-stone-200/80 bg-white text-sm">
                    @forelse ($tenantsNeedingAttention as $t)
                        <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3.5 transition hover:bg-stone-50/60">
                            <a class="font-medium text-primary hover:underline" href="{{ route('platform.tenants.show', $t) }}">{{ $t->name }}</a>
                            <span class="text-stone-600">{{ $t->status }}</span>
                        </li>
                    @empty
                        <li class="px-4 py-10 text-center text-sm text-stone-500">{{ __('No urgent items.') }}</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
@endsection
