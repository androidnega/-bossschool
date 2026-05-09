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
                'layout' => 'tile',
            ],
            [
                'href' => route('platform.plans.index'),
                'icon' => 'fa-solid fa-layer-group',
                'label' => __('Manage plans'),
                'style' => 'default',
                'layout' => 'tile',
            ],
            [
                'href' => route('platform.subscriptions'),
                'icon' => 'fa-solid fa-file-contract',
                'label' => __('Subscriptions'),
                'style' => 'default',
                'layout' => 'tile',
            ],
            [
                'href' => route('platform.feature-toggles.index'),
                'icon' => 'fa-solid fa-toggle-on',
                'label' => __('Feature toggles'),
                'style' => 'default',
                'layout' => 'tile',
            ],
            [
                'href' => route('platform.maintenance.index'),
                'icon' => 'fa-solid fa-road-barrier',
                'label' => __('Maintenance'),
                'style' => 'default',
                'layout' => 'tag',
            ],
            [
                'href' => route('platform.activity-logs.index'),
                'icon' => 'fa-solid fa-clock-rotate-left',
                'label' => __('Activity logs'),
                'style' => 'default',
                'layout' => 'tag',
            ],
            [
                'href' => route('platform.settings.index'),
                'icon' => 'fa-solid fa-sliders',
                'label' => __('Platform settings'),
                'style' => 'default',
                'layout' => 'tag',
            ],
            [
                'href' => route('platform.reset.index'),
                'icon' => 'fa-solid fa-rotate-left',
                'label' => __('Reset tools'),
                'style' => 'danger',
                'layout' => 'tag',
            ],
        ];

        $quickTileLinks = collect($quickLinks)->where('layout', 'tile')->values()->all();
        $quickTagLinks = collect($quickLinks)->where('layout', 'tag')->values()->all();

        $tenantStatusClass = static function (string $status): string {
            return match (\Illuminate\Support\Str::lower($status)) {
                'active' => 'bg-emerald-500/15 text-emerald-800 ring-1 ring-emerald-500/20',
                'suspended' => 'bg-rose-500/15 text-rose-800 ring-1 ring-rose-500/20',
                'trial' => 'bg-amber-500/15 text-amber-900 ring-1 ring-amber-400/25',
                default => 'bg-stone-500/10 text-stone-700 ring-1 ring-stone-400/15',
            };
        };
    @endphp

    <div class="rounded-3xl bg-stone-100/60 p-4 ring-1 ring-stone-200/50 sm:p-6">
    <div class="mx-auto max-w-6xl space-y-8 pb-2">
        {{-- Hero (sticks under the app top bar while the rest of the page scrolls) --}}
        <header class="glass-panel sticky top-0 z-20 rounded-3xl p-6 sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 space-y-4">
                    <div class="inline-flex items-center gap-2 rounded-full border border-stone-200/80 bg-white/60 px-3 py-1.5 text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-stone-600">
                        <span class="size-1.5 shrink-0 rounded-full bg-primary" aria-hidden="true"></span>
                        {{ __('Platform control') }}
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">{{ __('Platform dashboard') }}</h1>
                        <p class="max-w-xl text-sm leading-relaxed text-stone-600 sm:text-[15px]">
                            {{ __('Tenants, subscriptions, and platform health — not school day-to-day data.') }}
                        </p>
                    </div>
                </div>
                <div class="flex shrink-0 flex-col gap-2 sm:flex-row lg:flex-col">
                    <a href="{{ route('platform.tenants.index') }}" class="glass-tile glass-panel-subtle inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold text-stone-800">
                        <i class="fa-solid fa-school text-primary" aria-hidden="true"></i>
                        {{ __('All schools') }}
                    </a>
                    <a href="{{ route('platform.subscriptions') }}" class="glass-tile glass-panel-subtle inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold text-stone-800">
                        <i class="fa-solid fa-chart-line text-secondary" aria-hidden="true"></i>
                        {{ __('Billing pulse') }}
                    </a>
                </div>
            </div>
        </header>

        {{-- Quick actions: tile row + slim tag shortcuts --}}
        <section class="space-y-3" aria-labelledby="platform-quick-actions">
            <h2 id="platform-quick-actions" class="text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-stone-500">{{ __('Quick actions') }}</h2>
            <div class="space-y-2.5">
                <ul class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($quickTileLinks as $link)
                        @php
                            $isPrimary = $link['style'] === 'primary';
                            $isDanger = $link['style'] === 'danger';
                            $tileLayout = 'flex min-h-[5.25rem] flex-col justify-between gap-3 rounded-2xl border p-4 text-left transition-colors duration-150 sm:min-h-[5.5rem] sm:p-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2';
                            if ($isPrimary) {
                                $classes = $tileLayout.' border-primary/35 bg-primary text-white hover:border-primary/50 hover:bg-primary/90 focus-visible:ring-primary/50';
                            } elseif ($isDanger) {
                                $classes = $tileLayout.' border-rose-200/70 bg-rose-50/90 text-rose-950 hover:border-rose-300/90 hover:bg-rose-50 focus-visible:ring-rose-400/40';
                            } else {
                                $classes = $tileLayout.' glass-tile glass-panel-subtle border-stone-200/70 text-stone-800';
                            }
                            $iconWrap = $isPrimary
                                ? 'flex size-10 shrink-0 items-center justify-center rounded-xl bg-white/20 text-white ring-1 ring-white/25'
                                : ($isDanger
                                    ? 'flex size-10 shrink-0 items-center justify-center rounded-xl bg-white/80 text-rose-600 ring-1 ring-rose-200/50'
                                    : 'flex size-10 shrink-0 items-center justify-center rounded-xl bg-white/90 text-primary ring-1 ring-stone-200/60');
                        @endphp
                        <li>
                            <a href="{{ $link['href'] }}" class="{{ $classes }}">
                                <span class="{{ $iconWrap }}">
                                    <i class="{{ $link['icon'] }} text-sm" aria-hidden="true"></i>
                                </span>
                                <span class="text-xs font-semibold leading-snug sm:text-[13px] {{ $isPrimary ? 'text-white' : '' }}">{{ $link['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p id="platform-quick-tags-label" class="sr-only">{{ __('Platform shortcuts') }}</p>
                <ul class="flex flex-wrap gap-2" role="list" aria-labelledby="platform-quick-tags-label">
                    @foreach ($quickTagLinks as $link)
                        @php
                            $isDanger = $link['style'] === 'danger';
                            if ($isDanger) {
                                $tagClasses = 'inline-flex items-center gap-1.5 rounded-full border border-rose-200/80 bg-rose-50/90 py-1.5 pl-2.5 pr-3 text-xs font-semibold text-rose-900 transition-colors duration-150 hover:border-rose-300 hover:bg-rose-100/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400/45 focus-visible:ring-offset-2';
                            } else {
                                $tagClasses = 'inline-flex items-center gap-1.5 rounded-full border border-stone-200/80 bg-white py-1.5 pl-2.5 pr-3 text-xs font-semibold text-stone-700 transition-colors duration-150 hover:border-stone-300/90 hover:bg-stone-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/35 focus-visible:ring-offset-2';
                            }
                        @endphp
                        <li>
                            <a href="{{ $link['href'] }}" class="{{ $tagClasses }}">
                                <i class="{{ $link['icon'] }} text-[11px] opacity-80" aria-hidden="true"></i>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- Metrics — operational signals only --}}
        <section class="space-y-3" aria-labelledby="platform-metrics">
            <h2 id="platform-metrics" class="text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-stone-500">{{ __('Overview') }}</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <x-dash-card icon="fa-solid fa-school" :label="__('Total schools')" :value="number_format($tenantCount)" variant="platform" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60" />
                <x-dash-card icon="fa-solid fa-circle-check" :label="__('Active schools')" :value="number_format($activeSchools)" variant="revenue" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60" />
                <x-dash-card icon="fa-solid fa-ban" :label="__('Suspended')" :value="number_format($suspendedSchools)" variant="danger" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60" />
                <x-dash-card icon="fa-solid fa-hourglass-half" :label="__('Trial schools')" :value="number_format($trialSchools)" variant="debtors" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60" />
                <x-dash-card icon="fa-solid fa-file-signature" :label="__('Active subscriptions')" :value="number_format($activeSubscriptions)" variant="results" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60" />
                <x-dash-card icon="fa-solid fa-calendar-xmark" :label="__('Expired subscriptions')" :value="number_format($expiredSubscriptions)" variant="debtors" class="glass-tile glass-panel-subtle !rounded-2xl !border-stone-200/60" />
            </div>
        </section>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <section class="glass-panel rounded-2xl p-0">
                <h2 class="border-b border-stone-200/60 px-4 py-3 text-sm font-semibold text-stone-900">{{ __('Recent tenant registrations') }}</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-stone-200/50 bg-stone-50/95 text-[0.65rem] font-semibold uppercase tracking-wide text-stone-500">
                            <tr>
                                <th class="px-4 py-3">{{ __('School') }}</th>
                                <th class="px-4 py-3">{{ __('Status') }}</th>
                                <th class="px-4 py-3">{{ __('Created') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200/40">
                            @forelse ($recentTenants as $t)
                                <tr class="transition hover:bg-white/50">
                                    <td class="px-4 py-3">
                                        <a class="font-medium text-primary hover:underline" href="{{ route('platform.tenants.show', $t) }}">{{ $t->name }}</a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $tenantStatusClass($t->status) }}">{{ $t->status }}</span>
                                    </td>
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

            <section class="glass-panel rounded-2xl p-0">
                <h2 class="border-b border-stone-200/60 px-4 py-3 text-sm font-semibold text-stone-900">{{ __('Recent subscription changes') }}</h2>
                <ul class="divide-y divide-stone-200/40 text-sm">
                    @forelse ($recentSubscriptionChanges as $s)
                        <li class="flex flex-col gap-1 px-4 py-3.5 transition hover:bg-white/50 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-2">
                            <span class="font-medium text-stone-900">{{ $s->tenant?->name ?? '—' }}</span>
                            <span class="text-stone-600">{{ $s->plan?->name }} · <span class="font-medium text-primary">{{ $s->status }}</span></span>
                            <span class="text-xs text-stone-400 sm:shrink-0">{{ $s->updated_at?->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="px-4 py-10 text-center text-sm text-stone-500">{{ __('No subscription rows yet.') }}</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <section class="glass-panel rounded-2xl border-amber-200/50 p-0">
            <h2 class="border-b border-amber-200/40 bg-amber-50/80 px-4 py-3 text-sm font-semibold text-stone-900">{{ __('Tenants needing attention') }}</h2>
            <ul class="divide-y divide-stone-200/40 text-sm">
                @forelse ($tenantsNeedingAttention as $t)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3.5 transition hover:bg-white/50">
                        <a class="font-medium text-primary hover:underline" href="{{ route('platform.tenants.show', $t) }}">{{ $t->name }}</a>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $tenantStatusClass($t->status) }}">{{ $t->status }}</span>
                    </li>
                @empty
                    <li class="px-4 py-10 text-center text-sm text-stone-500">{{ __('No urgent items.') }}</li>
                @endforelse
            </ul>
        </section>
    </div>
    </div>
@endsection
