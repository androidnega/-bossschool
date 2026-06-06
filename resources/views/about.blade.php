@extends('layouts.marketing')

@section('body_class', 'home-shell bg-[#f4f8ff] font-sans text-slate-900 antialiased selection:bg-blue-600 selection:text-white overflow-x-hidden min-h-screen')

@section('title', __('About BossSchool'))

@section('meta_description', __('BossSchool is a modern school management platform built for Ghanaian schools — admissions, fees, results, communication and everyday administration in one calm workspace.'))

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
    </style>
@endsection

@section('content')
    <div class="flex min-h-[100dvh] flex-col">

        @include('layouts.partials.marketing-nav', ['active' => 'about'])

        <main class="relative z-10 flex-1">
            {{-- Intro --}}
            <section class="border-b border-slate-200/60 bg-white">
                <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
                    <div class="space-y-5 text-center">
                        <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-blue-700 sm:text-xs">
                            <i class="fa-solid fa-circle-info"></i>
                            {{ __('About BossSchool') }}
                        </span>
                        <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-[#0a1228] sm:text-4xl lg:text-5xl">
                            {{ __('A modern school platform built for') }}
                            <span class="gradient-text">{{ __('Ghanaian schools') }}</span>.
                        </h1>
                        <p class="mx-auto max-w-2xl text-base leading-relaxed text-slate-600 sm:text-lg">
                            {{ __('Boss School is a modern school management platform built specifically for Ghanaian schools to simplify admissions, fees, results, communication, and everyday administration.') }}
                        </p>
                    </div>
                </div>
            </section>

            {{-- Who we are --}}
            <section class="bg-[#f4f8ff]">
                <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
                    <div class="grid items-start gap-10 lg:grid-cols-12 lg:gap-14">
                        <div class="space-y-3 lg:col-span-5">
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-widest text-blue-600">{{ __('Who we are') }}</span>
                            <h2 class="text-2xl font-extrabold tracking-tight text-[#0a1228] sm:text-3xl">
                                {{ __('Built for everyday schools') }}
                            </h2>
                            <p class="text-sm leading-relaxed text-slate-600 sm:text-base">
                                {{ __('To make school management simple, affordable, and stress-free for private schools across Ghana and Africa.') }}
                            </p>
                        </div>
                        <div class="space-y-5 rounded-2xl border border-slate-200/70 bg-white p-6 shadow-sm sm:p-8 lg:col-span-7">
                            <p class="text-sm leading-relaxed text-slate-700 sm:text-base">
                                {{ __('We understand the daily challenges schools face — from tracking school fees and preparing results to communicating with parents and managing student records.') }}
                            </p>
                            <p class="text-sm font-semibold text-[#0a1228]">{{ __("That's why Boss School is designed to be:") }}</p>
                            <ul class="space-y-2.5 text-sm text-slate-700 sm:text-base">
                                @foreach ([
                                    __('Simple to use'),
                                    __('Mobile-friendly'),
                                    __('Fast and reliable'),
                                    __('Built around real school workflows'),
                                ] as $point)
                                    <li class="flex items-start gap-3">
                                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[11px] text-blue-700">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                        <span>{{ $point }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="border-t border-slate-100 pt-4 text-sm leading-relaxed text-slate-600 sm:text-base">
                                {{ __('Whether you run a small private school or a growing academy, Boss School helps you stay organized and in control.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Mission + Vision --}}
            <section class="bg-white">
                <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
                    <div class="grid gap-6 sm:gap-8 lg:grid-cols-2">

                        {{-- Mission --}}
                        <article class="rounded-2xl border border-slate-200/70 bg-[#f4f8ff] p-6 sm:p-8">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-600/30">
                                    <i class="fa-solid fa-bullseye"></i>
                                </span>
                                <h2 class="text-xl font-extrabold tracking-tight text-[#0a1228] sm:text-2xl">{{ __('Our mission') }}</h2>
                            </div>
                            <p class="mt-4 text-sm leading-relaxed text-slate-700 sm:text-base">
                                {{ __('Our mission is to help schools operate more efficiently through simple and reliable technology.') }}
                            </p>
                            <p class="mt-4 text-sm font-semibold text-[#0a1228]">{{ __('We aim to:') }}</p>
                            <ul class="mt-2 space-y-2 text-sm text-slate-700 sm:text-base">
                                @foreach ([
                                    __('Improve fee management and financial visibility'),
                                    __('Reduce administrative workload'),
                                    __('Simplify result preparation'),
                                    __('Strengthen communication between schools and parents'),
                                    __('Support the digital transformation of education in Ghana and Africa'),
                                ] as $point)
                                    <li class="flex items-start gap-3">
                                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-600"></span>
                                        <span>{{ $point }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="mt-5 border-t border-slate-200/70 pt-4 text-sm leading-relaxed text-slate-600">
                                {{ __('Boss School is committed to building technology that is practical, accessible, and impactful for everyday schools.') }}
                            </p>
                        </article>

                        {{-- Vision --}}
                        <article class="rounded-2xl border border-slate-200/70 bg-gradient-to-br from-[#0a1228] to-slate-900 p-6 text-white shadow-xl shadow-slate-950/10 sm:p-8">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                                    <i class="fa-solid fa-binoculars"></i>
                                </span>
                                <h2 class="text-xl font-extrabold tracking-tight sm:text-2xl">{{ __('Our vision') }}</h2>
                            </div>
                            <p class="mt-4 text-base leading-relaxed text-slate-200 sm:text-lg">
                                {{ __('To become the most trusted and easy-to-use school management platform powering schools across Africa.') }}
                            </p>

                            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="cta-grad inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white sm:w-auto">
                                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                        {{ __('Open dashboard') }}
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="cta-grad inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white sm:w-auto">
                                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                        {{ __('Sign in') }}
                                    </a>
                                @endauth
                                <a href="{{ route('contact') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10 sm:w-auto">
                                    <i class="fa-solid fa-envelope-open-text"></i>
                                    {{ __('Contact us') }}
                                </a>
                            </div>
                        </article>

                    </div>
                </div>
            </section>
        </main>

        @include('layouts.partials.marketing-footer')

    </div>
@endsection
