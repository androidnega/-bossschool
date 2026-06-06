@extends('layouts.marketing')

@section('body_class', 'home-shell bg-[#f4f8ff] font-sans text-slate-900 antialiased selection:bg-blue-600 selection:text-white overflow-x-hidden min-h-screen')

@section('title', __('Contact BossSchool'))

@section('meta_description', __('Reach BossSchool for demos, support, partnerships or general inquiries — Takoradi, Ghana.'))

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

        @include('layouts.partials.marketing-nav', ['active' => 'contact'])

        <main class="relative z-10 flex flex-1 items-center">
            <div class="mx-auto w-full max-w-5xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
                <div class="space-y-3 text-center">
                    <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-blue-700 sm:text-xs">
                        <i class="fa-solid fa-envelope"></i>
                        {{ __('Contact us') }}
                    </span>
                    <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-[#0a1228] sm:text-4xl lg:text-5xl">
                        {{ __("We'd love to") }}
                        <span class="gradient-text">{{ __('hear from you') }}</span>.
                    </h1>
                    <p class="mx-auto max-w-2xl text-base leading-relaxed text-slate-600 sm:text-lg">
                        {{ __('Reach out for demos, support, partnerships, or general inquiries.') }}
                    </p>
                </div>

                <div class="mt-10 grid gap-5 sm:gap-6 md:grid-cols-3">
                    {{-- Office --}}
                    <article class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-sm">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                            <i class="fa-solid fa-location-dot text-lg"></i>
                        </span>
                        <h2 class="mt-4 text-base font-bold text-[#0a1228]">{{ __('Office location') }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            {{ __('Takoradi, Western Region') }}<br>
                            {{ __('Ghana') }}
                        </p>
                        <a href="https://maps.google.com/?q=Takoradi%20Western%20Region%20Ghana" target="_blank" rel="noopener"
                           class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700">
                            {{ __('Open in Maps') }}
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </article>

                    {{-- Email --}}
                    <article class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-sm">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-700">
                            <i class="fa-solid fa-envelope text-lg"></i>
                        </span>
                        <h2 class="mt-4 text-base font-bold text-[#0a1228]">{{ __('Email') }}</h2>
                        <ul class="mt-2 space-y-1.5 text-sm">
                            <li>
                                <a href="mailto:hello@bossschoolapp.com" class="font-medium text-slate-700 hover:text-blue-600">hello@bossschoolapp.com</a>
                                <span class="block text-[11px] text-slate-400">{{ __('General inquiries') }}</span>
                            </li>
                            <li>
                                <a href="mailto:support@bossschoolapp.com" class="font-medium text-slate-700 hover:text-blue-600">support@bossschoolapp.com</a>
                                <span class="block text-[11px] text-slate-400">{{ __('Existing customers') }}</span>
                            </li>
                        </ul>
                    </article>

                    {{-- Phone / WhatsApp --}}
                    <article class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-sm">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                        </span>
                        <h2 class="mt-4 text-base font-bold text-[#0a1228]">{{ __('Phone / WhatsApp') }}</h2>
                        <a href="tel:+233541069241" class="mt-2 block text-sm font-semibold text-slate-700 hover:text-blue-600">+233 54 106 9241</a>
                        <a href="https://wa.me/233541069241?text={{ urlencode(__('Hello BossSchool, I would like more information.')) }}"
                           target="_blank" rel="noopener"
                           class="mt-3 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white transition hover:-translate-y-0.5 hover:bg-emerald-700">
                            <i class="fa-brands fa-whatsapp"></i>
                            {{ __('Chat on WhatsApp') }}
                        </a>
                    </article>
                </div>

                <div class="mt-10 flex flex-col items-center justify-center gap-3 rounded-2xl border border-slate-200/70 bg-white p-6 text-center shadow-sm sm:flex-row sm:gap-4 sm:p-8 sm:text-left">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-600/30">
                        <i class="fa-solid fa-handshake-angle text-lg"></i>
                    </span>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-[#0a1228] sm:text-lg">{{ __('Looking for a demo for your school?') }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ __('Tell us about your school and we will set up a sandbox tailored for you.') }}</p>
                    </div>
                    <a href="mailto:hello@bossschoolapp.com?subject=BossSchool%20demo%20request"
                       class="cta-grad inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white">
                        {{ __('Request a demo') }}
                        <i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                </div>
            </div>
        </main>

        @include('layouts.partials.marketing-footer')

    </div>
@endsection
