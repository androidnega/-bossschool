@extends('layouts.marketing')

@section('body_class', 'home-shell bg-[#f4f8ff] font-sans text-slate-900 antialiased selection:bg-blue-600 selection:text-white overflow-x-hidden min-h-screen')

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
        .home-shell .glow-anim { animation: home-pulse-slow 6s ease-in-out infinite; }
        .home-shell .glow-anim-2 { animation: home-pulse-slow 8s ease-in-out infinite 2s; }
        @keyframes home-ping { 75%, 100% { transform: scale(2.2); opacity: 0; } }
        .home-shell .live-ping { animation: home-ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite; }
        .home-shell ::-webkit-scrollbar { width: 8px; height: 8px; }
        .home-shell ::-webkit-scrollbar-track { background: #f4f8ff; }
        .home-shell ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .home-shell ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .home-shell .nav-pill a:hover { background-color: rgba(255,255,255,0.85); color: #0a1228; }
    </style>
@endsection

@section('content')
    {{-- Top-area glowing background accents --}}
    <div class="pointer-events-none absolute top-0 left-1/2 z-0 h-[520px] w-full max-w-7xl -translate-x-1/2 overflow-hidden opacity-50">
        <div class="glow-a glow-anim absolute" style="top:-150px; left:8%; width:600px; height:600px;"></div>
        <div class="glow-b glow-anim-2 absolute" style="top:-100px; right:5%; width:500px; height:500px;"></div>
    </div>

    {{-- ─────────────── Sticky pill nav ─────────────── --}}
    <header class="sticky top-0 z-50 border-b border-slate-200/50 bg-[#f4f8ff]/80 backdrop-blur-xl transition-all">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-2 px-4 sm:h-20 sm:gap-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-2 sm:gap-3">
                <div class="brand-mark flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-white transition-transform group-hover:scale-105 sm:h-11 sm:w-11 sm:rounded-xl">
                    <i class="fa-solid fa-graduation-cap text-base sm:text-xl"></i>
                </div>
                <span class="truncate text-base font-extrabold tracking-tight text-[#0a1228] sm:text-xl">
                    Boss<span class="gradient-text">School</span>
                </span>
            </a>

            <nav class="nav-pill hidden items-center gap-1 rounded-full border border-slate-200/30 bg-slate-200/40 p-1.5 backdrop-blur-sm md:flex">
                <a href="#modules" class="rounded-full px-5 py-2 text-sm font-semibold text-slate-600 transition-all">{{ __('Modules') }}</a>
                <a href="#roles" class="rounded-full px-5 py-2 text-sm font-semibold text-slate-600 transition-all">{{ __('Ecosystem') }}</a>
                <a href="#preview" class="rounded-full px-5 py-2 text-sm font-semibold text-slate-600 transition-all">{{ __('Live Engine') }}</a>
                <a href="#why" class="rounded-full px-5 py-2 text-sm font-semibold text-slate-600 transition-all">{{ __('Why BossSchool') }}</a>
            </nav>

            <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="hidden px-4 py-2 text-sm font-bold text-slate-700 transition-colors hover:text-blue-600 sm:inline-block">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="hidden px-4 py-2 text-sm font-bold text-slate-700 transition-colors hover:text-blue-600 sm:inline-block">{{ __('Sign In') }}</a>
                @endauth
                <a href="mailto:hello@bossschool.com?subject=BossSchool%20demo%20request" class="rounded-lg bg-[#0a1228] px-3 py-2 text-xs font-bold text-white shadow-lg shadow-slate-950/10 transition-all hover:-translate-y-0.5 hover:bg-slate-900 active:translate-y-0 sm:rounded-xl sm:px-5 sm:py-3 sm:text-sm">
                    <span class="hidden sm:inline">{{ __('Book Demo') }}</span>
                    <span class="sm:hidden">{{ __('Demo') }}</span>
                </a>
            </div>
        </div>
    </header>

    <main class="relative z-10">

        {{-- ─────────────── Hero ─────────────── --}}
        <section class="pt-6 pb-12 sm:pt-10 sm:pb-16 lg:pt-16 lg:pb-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-10 sm:gap-12 lg:grid-cols-12 lg:gap-8">
                    {{-- Left copy --}}
                    <div class="space-y-5 text-center sm:space-y-6 lg:col-span-5 lg:text-left">
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200/80 bg-white px-3 py-1.5 text-[11px] font-bold text-blue-700 shadow-sm sm:text-xs">
                            <span class="relative flex h-2 w-2 shrink-0">
                                <span class="live-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            </span>
                            {{ __('Built for Ghanaian basic schools') }}
                        </div>

                        <h1 class="text-3xl font-extrabold leading-[1.1] tracking-tight text-[#0a1228] sm:text-5xl sm:leading-[1.05] lg:text-[56px]">
                            {{ __('The intelligence engine behind modern') }}
                            <span class="gradient-text">{{ __('academies') }}</span>.
                        </h1>

                        <p class="mx-auto max-w-xl text-base leading-relaxed text-slate-600 sm:text-lg lg:mx-0">
                            {{ __('One quiet workspace for admissions, fees, attendance, results, report cards and parent messages — designed for primary and JHS schools in Ghana.') }}
                        </p>

                        <div class="flex flex-col items-stretch justify-center gap-3 pt-1 sm:flex-row sm:items-center sm:gap-3.5 sm:pt-2 lg:justify-start">
                            @auth
                                <a href="{{ route('dashboard') }}" class="cta-grad w-full rounded-xl px-6 py-3.5 text-center text-sm font-bold text-white sm:w-auto sm:px-8 sm:py-4">
                                    {{ __('Open Dashboard') }}
                                </a>
                            @else
                                <a href="mailto:hello@bossschool.com?subject=BossSchool%20demo%20request" class="cta-grad w-full rounded-xl px-6 py-3.5 text-center text-sm font-bold text-white sm:w-auto sm:px-8 sm:py-4">
                                    {{ __('Start Setup Wizard') }}
                                </a>
                            @endauth
                            <a href="#preview" class="group flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-center text-sm font-bold text-slate-800 shadow-sm transition-all hover:bg-slate-50 sm:w-auto sm:px-8 sm:py-4">
                                <i class="fa-solid fa-circle-play text-blue-600 transition-transform group-hover:scale-110"></i>
                                {{ __('Live preview') }}
                            </a>
                        </div>

                        <div class="mx-auto grid max-w-md grid-cols-3 gap-3 border-t border-slate-200/60 pt-5 sm:gap-4 sm:pt-6 lg:mx-0">
                            <div class="min-w-0">
                                <span class="block text-xl font-extrabold text-[#0a1228] sm:text-2xl">99.9%</span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 sm:text-xs">{{ __('Uptime SLA') }}</span>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-xl font-extrabold text-[#0a1228] sm:text-2xl">&lt; 3ms</span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 sm:text-xs">{{ __('Load sync') }}</span>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-xl font-extrabold text-[#0a1228] sm:text-2xl">256-bit</span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 sm:text-xs">{{ __('Encryption') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right interactive dashboard mock --}}
                    <div id="preview" class="relative lg:col-span-7">
                        <div class="absolute -inset-2 rounded-3xl bg-gradient-to-r from-blue-600 to-cyan-400 opacity-10 blur-2xl"></div>

                        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl backdrop-blur-sm">
                            {{-- Sleek tab bar --}}
                            <div class="flex items-center justify-between gap-2 bg-slate-900 px-3 py-3 sm:px-5 sm:py-4">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-slate-700"></span>
                                    <span class="h-3 w-3 rounded-full bg-slate-700"></span>
                                    <span class="h-3 w-3 rounded-full bg-slate-700"></span>
                                    <div class="ml-1 hidden min-w-0 items-center gap-2 rounded-md bg-slate-800 px-3 py-1 font-mono text-[10px] text-slate-400 sm:ml-3 sm:flex">
                                        <i class="fa-solid fa-lock text-[8px] text-emerald-400"></i>
                                        <span class="truncate">core-analytics.bossschool.io</span>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded bg-blue-600 px-2 py-0.5 text-[10px] font-bold text-white sm:text-xs">{{ __('Live preview') }}</span>
                            </div>

                            <div class="grid grid-cols-12 bg-slate-50/50">
                                {{-- Sidebar mock --}}
                                <aside class="col-span-3 hidden space-y-4 border-r border-slate-200/60 bg-white p-4 sm:block">
                                    <div class="space-y-1.5">
                                        <div class="flex h-7 items-center gap-2 rounded-lg bg-blue-50 px-2 text-[11px] font-bold text-blue-700">
                                            <i class="fa-solid fa-chart-pie"></i> {{ __('Control center') }}
                                        </div>
                                        <div class="flex h-7 items-center gap-2 rounded-lg px-2 text-[11px] font-semibold text-slate-500 hover:bg-slate-50">
                                            <i class="fa-solid fa-users"></i> {{ __('Student roster') }}
                                        </div>
                                        <div class="flex h-7 items-center gap-2 rounded-lg px-2 text-[11px] font-semibold text-slate-500 hover:bg-slate-50">
                                            <i class="fa-solid fa-credit-card"></i> {{ __('Fee management') }}
                                        </div>
                                        <div class="flex h-7 items-center gap-2 rounded-lg px-2 text-[11px] font-semibold text-slate-500 hover:bg-slate-50">
                                            <i class="fa-solid fa-sliders"></i> {{ __('System settings') }}
                                        </div>
                                    </div>
                                    <div class="border-t border-slate-100 pt-4">
                                        <div class="rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 p-2.5 text-center text-[10px] text-white">
                                            <p class="font-bold">{{ __('Term storage') }}</p>
                                            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-white/20">
                                                <div class="h-full w-2/3 rounded-full bg-white"></div>
                                            </div>
                                        </div>
                                    </div>
                                </aside>

                                {{-- Work area --}}
                                <main class="col-span-12 space-y-4 p-3 sm:col-span-9 sm:space-y-5 sm:p-5">
                                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                                        <div class="min-w-0 space-y-1 rounded-xl border border-slate-200/80 bg-white p-2.5 shadow-sm sm:p-3.5">
                                            <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400 sm:text-[10px]">{{ __('Total intake') }}</span>
                                            <div class="flex flex-wrap items-baseline justify-between gap-1">
                                                <span class="text-base font-extrabold tracking-tight text-[#0a1228] sm:text-xl">2,840</span>
                                                <span class="rounded bg-emerald-50 px-1 text-[9px] font-bold text-emerald-600 sm:text-[10px]">+4.2%</span>
                                            </div>
                                        </div>
                                        <div class="min-w-0 space-y-1 rounded-xl border border-slate-200/80 bg-white p-2.5 shadow-sm sm:p-3.5">
                                            <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400 sm:text-[10px]">{{ __('Daily attendance') }}</span>
                                            <div class="flex flex-wrap items-baseline justify-between gap-1">
                                                <span class="text-base font-extrabold tracking-tight text-[#0a1228] sm:text-xl">94.2%</span>
                                                <span class="rounded bg-blue-50 px-1 text-[9px] font-bold text-blue-600 sm:text-[10px]">{{ __('Optimal') }}</span>
                                            </div>
                                        </div>
                                        <div class="min-w-0 space-y-1 rounded-xl border border-slate-200/80 bg-white p-2.5 shadow-sm sm:p-3.5">
                                            <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400 sm:text-[10px]">{{ __('Net revenue') }}</span>
                                            <div class="flex flex-wrap items-baseline justify-between gap-1">
                                                <span class="text-base font-extrabold tracking-tight text-emerald-600 sm:text-xl">88%</span>
                                                <span class="rounded bg-amber-50 px-1 text-[9px] font-bold text-amber-600 sm:text-[10px]">{{ __('Pending') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-4 rounded-xl border border-slate-200/80 bg-white p-3 shadow-sm sm:p-4">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="min-w-0">
                                                <h4 class="text-xs font-bold text-[#0a1228]">{{ __('Revenue tracking against term goal') }}</h4>
                                                <p class="hidden text-[10px] text-slate-400 sm:block">{{ __('Automated multi-ledger updates · synced 2m ago') }}</p>
                                            </div>
                                            <span class="shrink-0 rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600">{{ __('Live stream') }}</span>
                                        </div>

                                        <div class="flex h-28 w-full items-end gap-3 border-b border-slate-100 px-2 pt-4">
                                            <div class="relative h-[40%] w-full rounded-t-md bg-slate-100 transition-colors hover:bg-slate-200"></div>
                                            <div class="relative h-[65%] w-full rounded-t-md bg-slate-100 transition-colors hover:bg-slate-200"></div>
                                            <div class="relative h-[92%] w-full rounded-t-md bg-gradient-to-t from-blue-600 to-cyan-500">
                                                <div class="absolute -top-6 left-1/2 -translate-x-1/2 rounded bg-[#0a1228] px-1 py-0.5 text-[8px] font-bold text-white">92%</div>
                                            </div>
                                            <div class="relative h-[50%] w-full rounded-t-md bg-slate-100 transition-colors hover:bg-slate-200"></div>
                                            <div class="relative h-[78%] w-full rounded-t-md bg-gradient-to-t from-sky-600 to-cyan-400"></div>
                                        </div>
                                        <div class="flex items-center justify-between px-1 text-[9px] font-bold text-slate-400">
                                            <span>{{ __('Primary 1') }}</span>
                                            <span>{{ __('Primary 4') }}</span>
                                            <span class="text-blue-600">{{ __('Primary 6') }}</span>
                                            <span>{{ __('JHS 2') }}</span>
                                            <span>{{ __('JHS 3') }}</span>
                                        </div>
                                    </div>
                                </main>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─────────────── Feature pillars ─────────────── --}}
        <section id="modules" class="relative border-y border-slate-200/60 bg-white py-14 sm:py-20 lg:py-24">
            <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl space-y-4 text-center sm:mb-16 lg:mb-20">
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-widest text-blue-600">{{ __('Robust infrastructure') }}</span>
                    <h2 class="text-3xl font-extrabold tracking-tight text-[#0a1228] sm:text-4xl">
                        {{ __('Engineered for modern multi-campus workflows.') }}
                    </h2>
                    <p class="text-base leading-relaxed text-slate-500 sm:text-lg">
                        {{ __('Say goodbye to clumsy legacy tools. Responsive workflows tailored for daily operations, administration and digital record keeping.') }}
                    </p>
                </div>

                @php
                    $modules = [
                        ['fa-id-card',         __('Unified identity & rosters'), __('Manage admissions, profiles, document uploads and emergency contacts inside a single hub.')],
                        ['fa-vault',           __('Fee ledgers & MoMo gateway'), __('Configure discount tiers, auto-generate invoices, accept MTN/Telecel/AirtelTigo and eliminate ledger discrepancies.')],
                        ['fa-wand-magic-sparkles', __('Automated report builder'), __('Populate score matrices, compute weights and publish printable Ghanaian report cards effortlessly.')],
                        ['fa-clipboard-user',  __('Attendance & registers'),    __('Daily class and staff attendance with parent-visible summaries and SMS alerts on absences.')],
                        ['fa-comments',        __('Messaging & SMS'),           __('Reach a class, a parent or the whole school in seconds — fees, attendance, notices and emergencies.')],
                        ['fa-shield-halved',   __('Multi-tenant by design'),    __('Every school is isolated. Your students, your fees, your data — never mixed with another school.')],
                    ];
                @endphp

                <div class="grid gap-5 p-1 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3">
                    @foreach ($modules as [$icon, $title, $copy])
                        <div class="group rounded-2xl border border-slate-200/70 bg-[#f4f8ff] p-6 shadow-sm transition-all duration-300 hover:border-blue-200 hover:bg-white hover:shadow-xl hover:shadow-blue-600/5 sm:p-8">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-slate-100 bg-white text-blue-600 shadow-md transition-transform group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white sm:mb-6">
                                <i class="fa-solid {{ $icon }} text-lg"></i>
                            </div>
                            <h3 class="mb-2 text-lg font-bold text-[#0a1228] sm:mb-3">{{ $title }}</h3>
                            <p class="text-sm leading-relaxed text-slate-600">{{ $copy }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ─────────────── Ecosystem (roles) ─────────────── --}}
        <section id="roles" class="bg-[#f4f8ff] py-14 sm:py-20 lg:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-10 flex flex-col justify-between gap-4 sm:mb-16 md:flex-row md:items-end">
                    <div class="max-w-xl space-y-3 text-center md:text-left">
                        <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold uppercase tracking-widest text-sky-600">{{ __('Role management') }}</span>
                        <h2 class="text-3xl font-extrabold tracking-tight text-[#0a1228] sm:text-4xl">
                            {{ __('Dedicated interfaces. Custom permissions.') }}
                        </h2>
                    </div>
                    <p class="max-w-xs text-center text-sm font-medium text-slate-500 md:text-right">
                        {{ __('Every system profile enjoys optimized views, distinct access boundaries and specialized action layouts.') }}
                    </p>
                </div>

                @php
                    $rolesGrid = [
                        ['01', __('Super Administration'), __('Full visibility over campus finances, system roles, audit logs and configuration layers.'), 'bg-blue-600',   'bg-blue-50 text-blue-600',   'text-blue-600 group-hover:text-blue-700'],
                        ['02', __('Educators & Faculty'),  __('Log attendance, modify assignment schedules, track grading marks and leave progress notes.'),     'bg-sky-600',    'bg-sky-50 text-sky-600',     'text-sky-600 group-hover:text-sky-700'],
                        ['03', __('Parents & Guardians'),  __('Monitor progress cards, check behaviour logs, verify attendance and pay outstanding fees instantly.'), 'bg-emerald-600','bg-emerald-50 text-emerald-600','text-emerald-600 group-hover:text-emerald-700'],
                        ['04', __('Student Terminal'),     __('Track assigned tasks, download shared resources, check historic grades and lecture timetables.'),  'bg-amber-600',  'bg-amber-50 text-amber-600', 'text-amber-600 group-hover:text-amber-700'],
                    ];
                @endphp

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($rolesGrid as [$num, $title, $copy, $bar, $chip, $link])
                        <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-6 shadow-sm transition-shadow hover:shadow-lg">
                            <div class="absolute left-0 top-0 h-[3px] w-full {{ $bar }}"></div>
                            <div class="space-y-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-bold {{ $chip }}">{{ $num }}</div>
                                <h4 class="text-lg font-bold text-[#0a1228]">{{ $title }}</h4>
                                <p class="text-xs leading-relaxed text-slate-500">{{ $copy }}</p>
                            </div>
                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-6 text-xs font-bold {{ $link }}">
                                {{ __('Launch interface') }}
                                <i class="fa-solid fa-arrow-right-long transition-transform group-hover:translate-x-1"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ─────────────── How a term flows ─────────────── --}}
        <section class="bg-white py-14 sm:py-20 lg:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-10 max-w-2xl space-y-3 text-center sm:mb-16">
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-widest text-blue-600">{{ __('A term, end to end') }}</span>
                    <h2 class="text-3xl font-extrabold tracking-tight text-[#0a1228] sm:text-4xl">{{ __('From day one to report cards.') }}</h2>
                </div>

                @php
                    $steps = [
                        [__('Open the term'),     __('Set the academic year, term dates and classes. Carry pupils over or admit new ones.')],
                        [__('Invoice & collect'), __('Generate fee invoices, accept MoMo and cash, and watch debtors fall in real time.')],
                        [__('Run the day'),       __('Teachers take attendance and record scores. Admins handle the rest from one place.')],
                        [__('Publish & message'), __('Validate results, publish report cards as PDF and notify every parent in one click.')],
                    ];
                @endphp

                <ol class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    @foreach ($steps as [$title, $copy])
                        <li class="rounded-2xl border border-slate-200/70 bg-[#f4f8ff] p-6 transition-all hover:bg-white hover:shadow-xl hover:shadow-blue-600/5">
                            <span class="inline-block rounded-md bg-blue-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">{{ sprintf(__('Step %02d'), $loop->iteration) }}</span>
                            <h3 class="mt-4 text-lg font-bold leading-tight tracking-tight text-[#0a1228]">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $copy }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- ─────────────── Why BossSchool ─────────────── --}}
        <section id="why" class="bg-[#f4f8ff] py-14 sm:py-20 lg:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-10 sm:gap-12 lg:grid-cols-[1fr_2fr] lg:gap-20">
                    <div class="space-y-4">
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-widest text-blue-600">{{ __('Why BossSchool') }}</span>
                        <h2 class="text-3xl font-extrabold tracking-tight text-[#0a1228] sm:text-4xl">
                            {{ __('Built for Ghanaian schools — not retrofitted.') }}
                        </h2>
                    </div>
                    @php
                        $reasons = [
                            [__('Mobile Money first'),       __('Payments, reconciliation and receipts are designed around MoMo workflows from day one — not bolted on.')],
                            [__('Ghana academic calendar'),  __('Terms, mid-term breaks, promotion rules and report-card formats that match how schools actually run.')],
                            [__('Multi-tenant by design'),   __('Every school is isolated. Your students, your fees, your data — never mixed with another school.')],
                            [__('Calm UI, fast on slow networks'), __('Lean pages, sensible defaults, keyboard-friendly forms — usable on a mid-range Android during a brown-out.')],
                            [__('Bilingual-friendly'),       __('English by default with friendly, plain copy. Translations supported across the app.')],
                            [__('SMS where it matters'),     __('Fee reminders, attendance alerts and emergency notices reach parents on a feature phone — not just an inbox.')],
                        ];
                    @endphp
                    <dl class="grid gap-6 sm:grid-cols-2 sm:gap-8">
                        @foreach ($reasons as [$title, $copy])
                            <div>
                                <dt class="flex items-start gap-2 text-base font-bold text-[#0a1228]">
                                    <i class="fa-solid fa-check mt-1 shrink-0 text-blue-600"></i>
                                    <span class="min-w-0">{{ $title }}</span>
                                </dt>
                                <dd class="mt-2 pl-6 text-sm leading-relaxed text-slate-600">{{ $copy }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </section>

        {{-- ─────────────── Dark CTA banner ─────────────── --}}
        <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-16 lg:px-8">
            <div class="relative space-y-5 overflow-hidden rounded-2xl bg-[#0a1228] p-6 text-center shadow-2xl shadow-slate-950/20 sm:space-y-6 sm:rounded-3xl sm:p-12 lg:p-16">
                <div class="pointer-events-none absolute bottom-0 right-0 h-80 w-80 rounded-full bg-blue-600/25 blur-[80px]"></div>
                <div class="pointer-events-none absolute top-0 left-0 h-72 w-72 rounded-full bg-cyan-500/20 blur-[80px]"></div>

                <h3 class="relative mx-auto max-w-2xl text-xl font-extrabold leading-tight tracking-tight text-white sm:text-3xl lg:text-4xl">
                    {{ __("Ready to transform your school's administrative framework?") }}
                </h3>
                <p class="relative mx-auto max-w-xl text-sm leading-relaxed text-slate-400 sm:text-base">
                    {{ __('Get a sandbox configured for your school in under 10 minutes. No binding deployment paperwork required.') }}
                </p>
                <div class="relative pt-2 sm:pt-4">
                    <a href="mailto:hello@bossschool.com?subject=BossSchool%20sandbox" class="inline-block w-full rounded-xl bg-white px-6 py-3.5 text-sm font-extrabold text-[#0a1228] shadow-md transition-all hover:bg-slate-100 sm:w-auto sm:px-8 sm:py-4 sm:text-base">
                        {{ __('Request sandbox access') }}
                    </a>
                </div>
            </div>
        </section>

    </main>

    {{-- ─────────────── Footer ─────────────── --}}
    <footer class="border-t border-slate-200/80 bg-white py-12 text-sm text-slate-500">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 sm:flex-row sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <div class="brand-mark flex h-8 w-8 items-center justify-center rounded-lg text-white">
                    <i class="fa-solid fa-graduation-cap text-xs"></i>
                </div>
                <span class="text-base font-bold tracking-tight text-[#0a1228]">BossSchool</span>
            </div>
            <div class="flex flex-wrap justify-center gap-6 text-xs font-semibold text-slate-400">
                <a href="#modules" class="transition-colors hover:text-blue-600">{{ __('Modules') }}</a>
                <a href="#roles" class="transition-colors hover:text-blue-600">{{ __('Roles') }}</a>
                <a href="#why" class="transition-colors hover:text-blue-600">{{ __('Why BossSchool') }}</a>
                <a href="mailto:hello@bossschool.com" class="transition-colors hover:text-blue-600">{{ __('Contact') }}</a>
            </div>
            <p class="text-[11px] font-medium text-slate-400">&copy; {{ now()->year }} BossSchool · {{ __('Accra, Ghana') }}</p>
        </div>
    </footer>
@endsection
