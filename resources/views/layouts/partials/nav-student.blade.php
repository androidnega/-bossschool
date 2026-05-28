@php
    $navBase = 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-sky-800/80 transition-colors hover:bg-sky-50 hover:text-sky-950';
    $navActive = 'flex items-center gap-3 rounded-lg bg-sky-50 px-3 py-2.5 text-sm font-semibold text-sky-950 ring-1 ring-sky-200/70';
@endphp

<nav class="flex flex-1 flex-col gap-0.5 overflow-y-auto overscroll-contain p-3 text-sm" aria-label="{{ __('Student navigation') }}">
    <a href="{{ route('portal.student.index') }}" class="{{ request()->routeIs('portal.student.index') ? $navActive : $navBase }}">
        <i class="fa-solid fa-house w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
        <span>{{ __('Dashboard') }}</span>
    </a>
    <a href="{{ route('portal.student.report-card') }}" class="{{ request()->routeIs('portal.student.report-card') ? $navActive : $navBase }}">
        <i class="fa-solid fa-scroll w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
        <span>{{ __('Report card') }}</span>
    </a>
</nav>
