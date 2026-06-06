@php
    /**
     * Shared top nav for the marketing pages (home, about, contact).
     *
     * Mobile (<md): brand on the left + a single hamburger button on the
     *               right. Tapping it reveals a clean dropdown with the
     *               nav links and the Sign in / Open dashboard CTA.
     * Desktop (md+): brand + pill nav in the middle + Sign in CTA on the
     *               right (the classic marketing-page layout).
     *
     * The mobile menu is built on a <details> element so it works even
     * without JavaScript. The tiny script at the bottom of this partial
     * just closes the menu when the user taps outside or hits Escape.
     *
     * @var ?string $active   One of 'home' | 'about' | 'contact'
     */
    $active = $active ?? null;
    $linkBase   = 'rounded-full px-4 py-2 text-sm font-semibold transition-all';
    $linkActive = $linkBase.' bg-white text-blue-700 shadow-sm';
    $linkIdle   = $linkBase.' text-slate-600 hover:text-[#0a1228]';

    $mobileItemBase   = 'flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-colors';
    $mobileItemActive = $mobileItemBase.' bg-blue-50 text-blue-700';
    $mobileItemIdle   = $mobileItemBase.' text-slate-700 hover:bg-slate-100';
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200/60 bg-[#f4f8ff]/85 backdrop-blur-xl">
    <div class="mx-auto flex h-14 w-full max-w-7xl items-center justify-between gap-3 px-4 sm:h-16 sm:px-6 lg:h-20 lg:px-8">

        {{-- Brand --}}
        <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-2 sm:gap-3" aria-label="BossSchool">
            <div class="brand-mark flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-white transition-transform group-hover:scale-105 lg:h-11 lg:w-11 lg:rounded-xl">
                <i class="fa-solid fa-graduation-cap text-base lg:text-xl"></i>
            </div>
            <span class="truncate text-base font-extrabold tracking-tight text-[#0a1228] sm:text-lg lg:text-xl">
                Boss<span class="gradient-text">School</span>
            </span>
        </a>

        {{-- Desktop pill nav (md+) --}}
        <nav class="hidden items-center gap-1 rounded-full border border-slate-200/40 bg-slate-200/40 p-1.5 backdrop-blur-sm md:flex" aria-label="{{ __('Primary') }}">
            <a href="{{ route('home') }}" class="{{ $active === 'home' ? $linkActive : $linkIdle }}">{{ __('Home') }}</a>
            <a href="{{ route('about') }}" class="{{ $active === 'about' ? $linkActive : $linkIdle }}">{{ __('About us') }}</a>
            <a href="{{ route('contact') }}" class="{{ $active === 'contact' ? $linkActive : $linkIdle }}">{{ __('Contact') }}</a>
        </nav>

        {{-- Desktop CTA (md+) --}}
        <div class="hidden shrink-0 items-center gap-3 md:flex">
            @auth
                <a href="{{ route('dashboard') }}" class="cta-grad rounded-xl px-5 py-2.5 text-sm font-bold text-white">
                    {{ __('Open dashboard') }}
                </a>
            @else
                <a href="{{ route('login') }}" class="cta-grad rounded-xl px-5 py-2.5 text-sm font-bold text-white">
                    {{ __('Sign in') }}
                </a>
            @endauth
        </div>

        {{-- Mobile hamburger (<md) --}}
        <details class="relative md:hidden" data-marketing-menu>
            <summary
                class="inline-flex size-10 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50 active:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 [&::-webkit-details-marker]:hidden"
                aria-label="{{ __('Open menu') }}">
                <i class="fa-solid fa-bars text-base" aria-hidden="true"></i>
            </summary>

            <div class="absolute right-0 top-full z-40 mt-2 w-[min(16rem,calc(100vw-2rem))] origin-top-right rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10 ring-1 ring-slate-900/5"
                 role="menu"
                 aria-label="{{ __('Site navigation') }}">
                <a href="{{ route('home') }}" class="{{ $active === 'home' ? $mobileItemActive : $mobileItemIdle }}" role="menuitem">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-house w-4 text-center text-[12px] text-slate-400"></i>
                        {{ __('Home') }}
                    </span>
                </a>
                <a href="{{ route('about') }}" class="{{ $active === 'about' ? $mobileItemActive : $mobileItemIdle }}" role="menuitem">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-info w-4 text-center text-[12px] text-slate-400"></i>
                        {{ __('About us') }}
                    </span>
                </a>
                <a href="{{ route('contact') }}" class="{{ $active === 'contact' ? $mobileItemActive : $mobileItemIdle }}" role="menuitem">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-envelope w-4 text-center text-[12px] text-slate-400"></i>
                        {{ __('Contact') }}
                    </span>
                </a>

                <div class="my-1.5 border-t border-slate-100"></div>

                @auth
                    <a href="{{ route('dashboard') }}" class="cta-grad mx-1 my-1 flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white" role="menuitem">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        {{ __('Open dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="cta-grad mx-1 my-1 flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white" role="menuitem">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        {{ __('Sign in') }}
                    </a>
                @endauth
            </div>
        </details>
    </div>
</header>

{{-- Tap-outside / Escape closes the mobile <details> menu. Pure progressive
     enhancement — without JS the menu still works, you just close it by
     tapping the hamburger again. --}}
<script>
    (function () {
        var el = document.querySelector('[data-marketing-menu]');
        if (! el) return;
        document.addEventListener('click', function (e) {
            if (el.open && ! el.contains(e.target)) el.open = false;
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && el.open) el.open = false;
        });
    })();
</script>
