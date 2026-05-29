@extends('layouts.marketing')

@section('body_class', 'home-shell bg-[#f4f8ff] font-sans text-slate-900 antialiased selection:bg-blue-600 selection:text-white overflow-x-hidden')

@section('title', __('A calm school management system for Ghana'))

@section('meta_description', __('BossSchool brings admissions, fees, attendance, results, report cards and parent messaging into one calm workspace — built for primary and JHS schools in Ghana.'))

@section('head_extra')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap">
    <style>
        .home-shell { font-family: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .home-shell .gradient-text {
            background: linear-gradient(90deg, #2563eb 0%, #0ea5e9 55%, #1d4ed8 100%);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .home-shell .brand-mark {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
            box-shadow: 0 14px 30px -10px rgba(37, 99, 235, 0.45);
        }
        .home-shell .cta-grad {
            background-image: linear-gradient(90deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 18px 40px -16px rgba(37, 99, 235, 0.55);
            transition: all .25s ease;
        }
        .home-shell .cta-grad:hover {
            background-image: linear-gradient(90deg, #1d4ed8 0%, #2563eb 100%);
            transform: translateY(-2px);
        }
        .home-shell .glow-a { position: absolute; pointer-events: none; border-radius: 9999px;
            filter: blur(120px); mix-blend-mode: multiply;
            background: radial-gradient(closest-side, rgba(147,197,253,0.55), rgba(191,219,254,0)); }
        .home-shell .glow-b { position: absolute; pointer-events: none; border-radius: 9999px;
            filter: blur(110px); mix-blend-mode: multiply;
            background: radial-gradient(closest-side, rgba(186,230,253,0.55), rgba(207,250,254,0)); }
        @keyframes home-pulse-slow {
            0%, 100% { opacity: 0.55; transform: translateY(0); }
            50%      { opacity: 0.85; transform: translateY(-10px); }
        }
        .home-shell .glow-anim   { animation: home-pulse-slow 6s ease-in-out infinite; }
        .home-shell .glow-anim-2 { animation: home-pulse-slow 8s ease-in-out infinite 2s; }
        @keyframes home-ping { 75%, 100% { transform: scale(2.2); opacity: 0; } }
        .home-shell .live-ping { animation: home-ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite; }
    </style>
@endsection

@section('content')
    {{-- Single-viewport home page. The wrapper is min-h-[100dvh] flex column;
         header is fixed-height, the hero flexes to fill the rest, and the
         footer is a tiny one-liner. No long landing-page sections. --}}
    <div class="relative flex min-h-[100dvh] flex-col">

        {{-- Soft glowing accents (decorative only) --}}
        <div class="pointer-events-none absolute inset-x-0 top-0 z-0 h-[420px] overflow-hidden opacity-50">
            <div class="glow-a glow-anim absolute" style="top:-150px; left:8%; width:520px; height:520px;"></div>
            <div class="glow-b glow-anim-2 absolute" style="top:-100px; right:5%; width:460px; height:460px;"></div>
        </div>

        {{-- ─────────────── Top nav ─────────────── --}}
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

                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="cta-grad rounded-lg px-3 py-2 text-xs font-bold text-white sm:rounded-xl sm:px-5 sm:py-2.5 sm:text-sm">
                            {{ __('Open dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden px-4 py-2 text-sm font-bold text-slate-700 transition-colors hover:text-blue-600 sm:inline-block">{{ __('Sign in') }}</a>
                        <a href="mailto:hello@bossschool.com?subject=BossSchool%20demo%20request" class="rounded-lg bg-[#0a1228] px-3 py-2 text-xs font-bold text-white shadow-lg shadow-slate-950/10 transition-all hover:-translate-y-0.5 hover:bg-slate-900 active:translate-y-0 sm:rounded-xl sm:px-5 sm:py-2.5 sm:text-sm">
                            <span class="hidden sm:inline">{{ __('Book demo') }}</span>
                            <span class="sm:hidden">{{ __('Demo') }}</span>
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- ─────────────── Hero (fills the remaining viewport) ─────────────── --}}
        <main class="relative z-10 flex flex-1 items-center">
            <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-14">
                <div class="grid items-center gap-8 sm:gap-10 lg:grid-cols-12 lg:gap-12">

                    {{-- Left copy --}}
                    <div class="space-y-5 text-center sm:space-y-6 lg:col-span-6 lg:text-left">
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200/80 bg-white px-3 py-1.5 text-[11px] font-bold text-blue-700 shadow-sm sm:text-xs">
                            <span class="relative flex h-2 w-2 shrink-0">
                                <span class="live-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            </span>
                            {{ __('Built for Ghanaian basic schools') }}
                        </div>

                        <h1 class="text-3xl font-extrabold leading-[1.1] tracking-tight text-[#0a1228] sm:text-5xl sm:leading-[1.05] lg:text-[52px]">
                            {{ __('A calm classroom companion for') }}
                            <span class="gradient-text">{{ __('every Ghanaian school') }}</span>.
                        </h1>

                        <p class="mx-auto max-w-xl text-base leading-relaxed text-slate-600 sm:text-lg lg:mx-0">
                            {{ __('Admissions, fees, attendance, results, report cards and parent messaging — one quiet workspace built for primary and JHS schools.') }}
                        </p>

                        <div class="flex flex-col items-stretch justify-center gap-3 pt-1 sm:flex-row sm:items-center sm:justify-center sm:gap-3.5 lg:justify-start">
                            @auth
                                <a href="{{ route('dashboard') }}" class="cta-grad w-full rounded-xl px-6 py-3.5 text-center text-sm font-bold text-white sm:w-auto sm:px-8 sm:py-4">
                                    {{ __('Open dashboard') }}
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="cta-grad w-full rounded-xl px-6 py-3.5 text-center text-sm font-bold text-white sm:w-auto sm:px-8 sm:py-4">
                                    {{ __('Sign in') }}
                                </a>
                                <a href="mailto:hello@bossschool.com?subject=BossSchool%20demo%20request" class="group flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-center text-sm font-bold text-slate-800 shadow-sm transition-all hover:bg-slate-50 sm:w-auto sm:px-8 sm:py-4">
                                    <i class="fa-solid fa-paper-plane text-blue-600 transition-transform group-hover:translate-x-0.5"></i>
                                    {{ __('Request a demo') }}
                                </a>
                            @endauth
                        </div>

                        <div class="mx-auto grid max-w-md grid-cols-3 gap-3 border-t border-slate-200/60 pt-5 sm:gap-4 sm:pt-6 lg:mx-0">
                            <div class="min-w-0">
                                <span class="block text-xl font-extrabold text-[#0a1228] sm:text-2xl">99.9%</span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 sm:text-xs">{{ __('Uptime') }}</span>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-xl font-extrabold text-[#0a1228] sm:text-2xl">MoMo</span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 sm:text-xs">{{ __('Native') }}</span>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-xl font-extrabold text-[#0a1228] sm:text-2xl">256-bit</span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 sm:text-xs">{{ __('Encryption') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right: classroom photo --}}
                    <div class="relative lg:col-span-6">
                        <div class="absolute -inset-2 rounded-3xl bg-gradient-to-r from-blue-600 to-cyan-400 opacity-10 blur-2xl"></div>
                        <picture>
                            <source srcset="{{ asset('images/classroom.webp') }}" type="image/webp">
                            <img
                                src="{{ asset('images/classroom.jpg') }}"
                                alt="{{ __('A Ghanaian JHS class in session, with the teacher leading a lesson on values and good habits.') }}"
                                width="1100"
                                height="825"
                                loading="eager"
                                decoding="async"
                                class="relative aspect-[4/3] w-full rounded-2xl object-cover shadow-2xl ring-1 ring-slate-200/70 sm:rounded-3xl">
                        </picture>
                    </div>

                </div>
            </div>
        </main>

        {{-- ─────────────── Minimal footer ─────────────── --}}
        <footer class="relative z-10 border-t border-slate-200/60 bg-white/60 backdrop-blur">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-3 text-[11px] font-medium text-slate-500 sm:flex-row sm:px-6 sm:text-xs lg:px-8">
                <p>&copy; {{ now()->year }} BossSchool · {{ __('Accra, Ghana') }}</p>
                <div class="flex items-center gap-4">
                    <a href="mailto:hello@bossschool.com" class="transition-colors hover:text-blue-600">{{ __('hello@bossschool.com') }}</a>
                    @guest
                        <a href="{{ route('login') }}" class="font-semibold transition-colors hover:text-blue-600">{{ __('Sign in') }}</a>
                    @endguest
                </div>
            </div>
        </footer>

    </div>
@endsection
