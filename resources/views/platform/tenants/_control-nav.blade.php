@php
    $t = $tenant;
    $link = 'inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50';
    $active = 'inline-flex items-center gap-2 rounded-lg border border-teal-300 bg-teal-50 px-3 py-2 text-sm font-semibold text-teal-900';
@endphp
<nav class="mb-6 flex flex-wrap gap-2" aria-label="{{ __('Tenant control sections') }}">
    <a href="{{ route('platform.tenants.show', $t) }}" class="{{ request()->routeIs('platform.tenants.show') ? $active : $link }}"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i>{{ __('Overview') }}</a>
    <a href="{{ route('platform.tenants.users.index', $t) }}" class="{{ request()->routeIs('platform.tenants.users.*') ? $active : $link }}"><i class="fa-solid fa-users" aria-hidden="true"></i>{{ __('Users') }}</a>
    <a href="{{ route('platform.tenants.students.index', $t) }}" class="{{ request()->routeIs('platform.tenants.students.*') ? $active : $link }}"><i class="fa-solid fa-user-graduate" aria-hidden="true"></i>{{ __('Students') }}</a>
    <a href="{{ route('platform.tenants.staff.index', $t) }}" class="{{ request()->routeIs('platform.tenants.staff.*') ? $active : $link }}"><i class="fa-solid fa-id-badge" aria-hidden="true"></i>{{ __('Staff') }}</a>
    <a href="{{ route('platform.tenants.finance.index', $t) }}" class="{{ request()->routeIs('platform.tenants.finance.*') ? $active : $link }}"><i class="fa-solid fa-coins" aria-hidden="true"></i>{{ __('Finance') }}</a>
    <a href="{{ route('platform.tenants.academics.index', $t) }}" class="{{ request()->routeIs('platform.tenants.academics.*') ? $active : $link }}"><i class="fa-solid fa-book" aria-hidden="true"></i>{{ __('Academics') }}</a>
    <a href="{{ route('platform.tenants.attendance.index', $t) }}" class="{{ request()->routeIs('platform.tenants.attendance.*') ? $active : $link }}"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>{{ __('Attendance') }}</a>
    <a href="{{ route('platform.tenants.messages.index', $t) }}" class="{{ request()->routeIs('platform.tenants.messages.*') ? $active : $link }}"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i>{{ __('Messages') }}</a>
    <a href="{{ route('platform.tenants.subscription.index', $t) }}" class="{{ request()->routeIs('platform.tenants.subscription.*') ? $active : $link }}"><i class="fa-solid fa-file-contract" aria-hidden="true"></i>{{ __('Subscription') }}</a>
    <a href="{{ route('platform.tenants.settings.index', $t) }}" class="{{ request()->routeIs('platform.tenants.settings.*') ? $active : $link }}"><i class="fa-solid fa-gear" aria-hidden="true"></i>{{ __('Settings') }}</a>
</nav>
