<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')BossSchool | @yield('title')@else BossSchool @endif</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @include('layouts.partials.head-assets')
</head>
<body class="h-[100dvh] overflow-hidden bg-stone-100 font-sans text-gray-900 antialiased">
    <div class="flex h-full overflow-hidden">
        {{-- Hidden checkbox drives the CSS-only mobile drawer.
             A small inline script below the layout adds Escape-to-close,
             body scroll-lock, and auto-close on nav-link tap. --}}
        <input type="checkbox" id="mobile-sidebar" class="peer sr-only" aria-label="{{ __('Toggle navigation') }}">

        {{-- Backdrop. Fades in/out smoothly; click-anywhere to close. --}}
        <label for="mobile-sidebar"
               class="invisible fixed inset-0 z-30 bg-stone-900/45 opacity-0 backdrop-blur-[2px] transition-opacity duration-200 ease-out peer-checked:visible peer-checked:opacity-100 lg:hidden"
               aria-hidden="true"></label>

        <aside id="app-drawer"
               class="fixed inset-y-0 left-0 z-40 flex w-[min(18rem,86vw)] max-w-[18rem] shrink-0 -translate-x-full flex-col border-r border-stone-200/90 bg-stone-50 shadow-xl shadow-stone-900/10 transition-transform duration-200 ease-out peer-checked:translate-x-0 lg:static lg:z-0 lg:w-[17rem] lg:max-w-[17rem] lg:translate-x-0 lg:shrink-0 lg:shadow-none"
               style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);"
               aria-label="{{ __('Primary navigation') }}">
            {{-- Mobile-only drawer header with explicit close affordance. --}}
            <div class="flex shrink-0 items-center justify-between border-b border-stone-200/80 px-4 py-3 lg:hidden">
                <span class="text-[0.65rem] font-semibold uppercase tracking-wider text-stone-500">{{ __('Menu') }}</span>
                <label for="mobile-sidebar"
                       class="inline-flex size-9 cursor-pointer items-center justify-center rounded-lg text-stone-500 transition hover:bg-stone-200/60 hover:text-stone-800 focus:outline-none focus:ring-2 focus:ring-primary/30"
                       aria-label="{{ __('Close menu') }}">
                    <i class="fa-solid fa-xmark text-base" aria-hidden="true"></i>
                </label>
            </div>

            <div class="flex min-h-0 flex-1 flex-col">
                @include('layouts.partials.app-sidebar')
            </div>
        </aside>

        <div class="flex min-h-0 min-w-0 flex-1 flex-col bg-white">
            <header class="flex shrink-0 items-center justify-between gap-3 border-b border-stone-200/90 bg-white px-3 py-2.5 sm:px-4 lg:px-6">
                <div class="flex min-w-0 flex-1 items-center gap-2.5 sm:gap-3">
                    <label for="mobile-sidebar"
                           class="inline-flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-stone-200 bg-white text-stone-600 transition hover:bg-stone-50 active:bg-stone-100 lg:hidden"
                           aria-label="{{ __('Open menu') }}"
                           aria-controls="app-drawer">
                        <i class="fa-solid fa-bars text-lg" aria-hidden="true"></i>
                    </label>
                    {{-- Brand mark (mobile only) — tappable, goes to /dashboard.
                         Replaces the empty header space on phones. --}}
                    <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center lg:hidden" aria-label="BossSchool">
                        <span class="flex size-8 items-center justify-center rounded-md bg-primary text-sm font-bold text-white" aria-hidden="true">B</span>
                    </a>
                    {{-- Page title — visible on every viewport, but truncates
                         gracefully on phones so it shares space with the brand. --}}
                    <span class="min-w-0 truncate text-sm font-semibold text-stone-800">@yield('header-title', '')</span>
                </div>
                <div class="flex shrink-0 items-center gap-2 text-sm sm:gap-3">
                    @include('layouts.partials.user-menu')
                </div>
            </header>

            <main class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain bg-white">
                <div class="p-3 sm:p-6 lg:p-8">
                    @if (session('status'))
                        <div class="mb-4 flex items-start gap-3 rounded-xl border border-secondary/30 bg-page-soft px-4 py-3 text-sm text-stone-800" role="status">
                            <i class="fa-solid fa-circle-check mt-0.5 text-secondary" aria-hidden="true"></i>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50/60 px-4 py-3 text-sm text-red-900" role="alert">
                            <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-600" aria-hidden="true"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50/60 px-4 py-3 text-sm text-red-900" role="alert">
                            <p class="flex items-center gap-2 font-medium">
                                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                                {{ __('Please correct the following:') }}
                            </p>
                            <ul class="mt-2 list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    {{-- Mobile sidebar polish. Three jobs:
         1. Auto-close the drawer when a nav link is tapped.
         2. Close on Escape (matches native dialog behavior).
         3. Lock body scroll while the drawer is open so the page
            underneath doesn't bounce on iOS Safari. --}}
    <script>
        (function () {
            var toggle = document.getElementById('mobile-sidebar');
            if (! toggle) return;

            var mobileQuery = window.matchMedia('(max-width: 1023px)');

            function isMobile() { return mobileQuery.matches; }

            function applyScrollLock() {
                if (toggle.checked && isMobile()) {
                    document.body.classList.add('drawer-open');
                } else {
                    document.body.classList.remove('drawer-open');
                }
            }

            // Auto-close on nav link tap.
            document.querySelectorAll('aside nav a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (isMobile()) toggle.checked = false;
                });
            });

            // Escape key closes the drawer.
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && toggle.checked && isMobile()) {
                    toggle.checked = false;
                    applyScrollLock();
                }
            });

            // Re-apply scroll lock whenever the toggle state or viewport
            // size changes (e.g. user rotates phone or resizes window).
            toggle.addEventListener('change', applyScrollLock);
            mobileQuery.addEventListener('change', applyScrollLock);
            applyScrollLock();
        })();
    </script>
    <style>
        /* Belt-and-braces scroll lock for iOS Safari. The body is already
           overflow-hidden but this makes 100% sure the page underneath
           doesn't rubber-band while the drawer is open. */
        body.drawer-open { overflow: hidden; touch-action: none; }
    </style>
</body>
</html>
