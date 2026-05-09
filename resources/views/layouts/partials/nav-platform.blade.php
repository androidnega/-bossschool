@php
    $navBase = 'flex items-center gap-3 rounded-xl px-3 py-2 text-[13px] font-medium text-stone-600 transition-colors hover:bg-stone-200/50 hover:text-stone-900';
    $navActive = 'flex items-center gap-3 rounded-xl bg-primary/10 px-3 py-2 text-[13px] font-semibold text-primary ring-1 ring-primary/15';
    $navActiveDanger = 'flex items-center gap-3 rounded-xl bg-rose-50 px-3 py-2 text-[13px] font-semibold text-rose-900 ring-1 ring-rose-200/80';
@endphp

<nav class="flex flex-1 flex-col gap-1 overflow-y-auto overscroll-contain rounded-2xl border border-stone-200/80 bg-white p-2.5 text-sm" aria-label="{{ __('Platform navigation') }}">
    <p class="px-3 pb-1 pt-1 text-[0.65rem] font-semibold uppercase tracking-wider text-stone-400">{{ __('Overview') }}</p>
    <a href="{{ route('platform.dashboard') }}" class="{{ request()->routeIs('platform.dashboard') ? $navActive : $navBase }}">
        <i class="fa-solid fa-gauge-high w-5 shrink-0 text-center text-[0.9rem] opacity-90" aria-hidden="true"></i>
        <span>{{ __('Dashboard') }}</span>
    </a>
    <a href="{{ route('platform.tenants.index') }}" class="{{ request()->routeIs('platform.tenants.*') ? $navActive : $navBase }}">
        <i class="fa-solid fa-school w-5 shrink-0 text-center text-[0.9rem] opacity-90" aria-hidden="true"></i>
        <span>{{ __('Tenants') }}</span>
    </a>

    <p class="mt-4 px-3 pb-1 pt-2 text-[0.65rem] font-semibold uppercase tracking-wider text-stone-400">{{ __('Billing') }}</p>
    <a href="{{ route('platform.plans.index') }}" class="{{ request()->routeIs('platform.plans.*') ? $navActive : $navBase }}">
        <i class="fa-solid fa-layer-group w-5 shrink-0 text-center text-[0.9rem] opacity-90" aria-hidden="true"></i>
        <span>{{ __('Plans') }}</span>
    </a>
    <a href="{{ route('platform.subscriptions') }}" class="{{ request()->routeIs('platform.subscriptions') ? $navActive : $navBase }}">
        <i class="fa-solid fa-file-contract w-5 shrink-0 text-center text-[0.9rem] opacity-90" aria-hidden="true"></i>
        <span>{{ __('Subscriptions') }}</span>
    </a>

    <p class="mt-4 px-3 pb-1 pt-2 text-[0.65rem] font-semibold uppercase tracking-wider text-stone-400">{{ __('Operations') }}</p>
    <a href="{{ route('platform.feature-toggles.index') }}" class="{{ request()->routeIs('platform.feature-toggles.*') ? $navActive : $navBase }}">
        <i class="fa-solid fa-toggle-on w-5 shrink-0 text-center text-[0.9rem] opacity-90" aria-hidden="true"></i>
        <span>{{ __('Feature toggles') }}</span>
    </a>
    <a href="{{ route('platform.maintenance.index') }}" class="{{ request()->routeIs('platform.maintenance.*') ? $navActive : $navBase }}">
        <i class="fa-solid fa-road-barrier w-5 shrink-0 text-center text-[0.9rem] opacity-90" aria-hidden="true"></i>
        <span>{{ __('Maintenance') }}</span>
    </a>
    <a href="{{ route('platform.activity-logs.index') }}" class="{{ request()->routeIs('platform.activity-logs.*') ? $navActive : $navBase }}">
        <i class="fa-solid fa-clock-rotate-left w-5 shrink-0 text-center text-[0.9rem] opacity-90" aria-hidden="true"></i>
        <span>{{ __('Activity logs') }}</span>
    </a>
    <a href="{{ route('platform.settings.index') }}" class="{{ request()->routeIs('platform.settings.*') ? $navActive : $navBase }}">
        <i class="fa-solid fa-sliders w-5 shrink-0 text-center text-[0.9rem] opacity-90" aria-hidden="true"></i>
        <span>{{ __('Platform settings') }}</span>
    </a>

    <p class="mt-4 px-3 pb-1 pt-2 text-[0.65rem] font-semibold uppercase tracking-wider text-rose-400">{{ __('Data') }}</p>
    <a href="{{ route('platform.reset.index') }}" class="{{ request()->routeIs('platform.reset.*') ? $navActiveDanger : $navBase }}">
        <i class="fa-solid fa-rotate-left w-5 shrink-0 text-center text-[0.9rem] text-rose-600 opacity-90" aria-hidden="true"></i>
        <span>{{ __('Reset tools') }}</span>
    </a>

    <p class="mt-4 px-3 pb-1 pt-2 text-[0.65rem] font-semibold uppercase tracking-wider text-stone-400">{{ __('Integrations') }}</p>
    <span class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2 text-[13px] font-medium text-stone-400" title="{{ __('Coming soon') }}">
        <i class="fa-solid fa-plug w-5 shrink-0 text-center text-[0.9rem] opacity-70" aria-hidden="true"></i>
        <span>{{ __('API Access') }}</span>
    </span>

    @can('viewPlatformNotices')
        <p class="mt-4 px-3 pb-1 pt-2 text-[0.65rem] font-semibold uppercase tracking-wider text-stone-400">{{ __('Comms') }}</p>
        <a href="{{ route('platform.notices.index') }}" class="{{ request()->routeIs('platform.notices.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-bullhorn w-5 shrink-0 text-center text-[0.9rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Platform notices') }}</span>
        </a>
    @endcan
</nav>
