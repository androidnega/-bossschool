@php
    /**
     * Shared top nav for the marketing pages (home, about, contact).
     * Pass `active` so the current page's link is highlighted.
     *
     * @var ?string $active
     */
    $active = $active ?? null;
    $linkBase   = 'rounded-full px-4 py-2 text-sm font-semibold transition-all';
    $linkActive = $linkBase . ' bg-white text-blue-700 shadow-sm';
    $linkIdle   = $linkBase . ' text-slate-600 hover:text-[#0a1228]';
@endphp

<header class="relative z-20 border-b border-slate-200/60 bg-[#f4f8ff]/80 backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-2 px-4 sm:h-20 sm:gap-3 sm:px-6 lg:px-8">

        <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-2 sm:gap-3">
            <div class="brand-mark flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-white transition-transform group-hover:scale-105 sm:h-11 sm:w-11 sm:rounded-xl">
                <i class="fa-solid fa-graduation-cap text-base sm:text-xl"></i>
            </div>
            <span class="truncate text-base font-extrabold tracking-tight text-[#0a1228] sm:text-xl">
                Boss<span class="gradient-text">School</span>
            </span>
        </a>

        <nav class="hidden items-center gap-1 rounded-full border border-slate-200/40 bg-slate-200/40 p-1.5 backdrop-blur-sm md:flex">
            <a href="{{ route('home') }}" class="{{ $active === 'home' ? $linkActive : $linkIdle }}">{{ __('Home') }}</a>
            <a href="{{ route('about') }}" class="{{ $active === 'about' ? $linkActive : $linkIdle }}">{{ __('About us') }}</a>
            <a href="{{ route('contact') }}" class="{{ $active === 'contact' ? $linkActive : $linkIdle }}">{{ __('Contact') }}</a>
        </nav>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="cta-grad rounded-lg px-3 py-2 text-xs font-bold text-white sm:rounded-xl sm:px-5 sm:py-2.5 sm:text-sm">
                    <span class="hidden sm:inline">{{ __('Open dashboard') }}</span>
                    <span class="sm:hidden">{{ __('Dashboard') }}</span>
                </a>
            @else
                <a href="{{ route('about') }}" class="rounded-lg px-3 py-2 text-xs font-bold text-slate-700 transition-colors hover:text-blue-600 md:hidden sm:rounded-xl sm:px-4 sm:py-2.5 sm:text-sm">
                    {{ __('About us') }}
                </a>
                <a href="{{ route('login') }}" class="cta-grad rounded-lg px-3 py-2 text-xs font-bold text-white sm:rounded-xl sm:px-5 sm:py-2.5 sm:text-sm">
                    {{ __('Sign in') }}
                </a>
            @endauth
        </div>
    </div>
</header>
