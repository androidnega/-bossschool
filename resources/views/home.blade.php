@extends('layouts.marketing')

@section('body_class', 'min-h-screen bg-canvas font-sans text-gray-900 antialiased')

@section('title', __('School management for modern schools'))

@section('content')
    <div class="flex min-h-screen flex-col bg-canvas">
        <header class="flex shrink-0 items-center justify-between gap-3 border-b border-stone-200/80 bg-white px-4 py-3 sm:px-6 lg:px-10">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-stone-900">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary text-sm font-bold text-white">B</span>
                <span class="text-base font-semibold tracking-tight text-stone-900">BoSchool</span>
            </a>
            <nav class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                @if ($loggedIn)
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white transition hover:bg-primary/95 sm:px-4">
                        <i class="fa-solid fa-gauge-high text-xs" aria-hidden="true"></i>
                        <span class="hidden sm:inline">{{ __('Dashboard') }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm font-medium text-stone-700 transition hover:bg-[#F8FAF7] sm:px-4">
                            <i class="fa-solid fa-right-from-bracket text-xs" aria-hidden="true"></i>
                            <span class="hidden sm:inline">{{ __('Log out') }}</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm font-medium text-stone-800 transition hover:bg-[#F8FAF7] sm:px-4">
                        <i class="fa-solid fa-right-to-bracket text-xs" aria-hidden="true"></i>
                        {{ __('Login') }}
                    </a>
                    <a href="mailto:hello@boschool.com?subject=BoSchool%20demo%20request" class="inline-flex items-center gap-1.5 rounded-lg bg-secondary px-3 py-2 text-sm font-semibold text-white transition hover:bg-secondary/95 sm:px-4">
                        <i class="fa-solid fa-calendar-check text-xs" aria-hidden="true"></i>
                        {{ __('Request demo') }}
                    </a>
                @endif
            </nav>
        </header>

        <main class="flex flex-col bg-canvas pt-10 sm:pt-12 lg:flex-row lg:items-start lg:pt-14 xl:pt-16">
            <div class="flex flex-col justify-center px-5 pb-10 pt-4 sm:px-8 sm:pb-12 sm:pt-6 lg:w-[52%] lg:shrink-0 lg:px-10 lg:pb-14 lg:pt-8 xl:px-14 xl:pb-16">
                <div class="mx-auto w-full max-w-xl">
                    <p class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('School management') }}</p>
                    <h1 class="mt-2 text-3xl font-bold leading-tight tracking-tight text-stone-900 sm:text-4xl">
                        {{ __('Smart School Management for Modern Schools') }}
                    </h1>
                    <p class="mt-4 text-base leading-relaxed text-stone-600 sm:text-lg">
                        {{ __('Manage students, fees, results, attendance, staff, parents, and reports from one clean dashboard.') }}
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        @if ($loggedIn)
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/95">
                                <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/95">
                                <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                                {{ __('Login') }}
                            </a>
                            <a href="mailto:hello@boschool.com?subject=BoSchool%20demo%20request" class="inline-flex items-center justify-center gap-2 rounded-lg border-2 border-primary bg-white px-5 py-2.5 text-sm font-semibold text-primary transition hover:bg-[#E6EEC9]/50">
                                <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                                {{ __('Request demo') }}
                            </a>
                        @endif
                    </div>

                    <div class="mt-8 flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-stone-200/90 bg-[#F8FAF7] px-3 py-1.5 text-xs font-medium text-stone-800 sm:text-sm">
                            <i class="fa-solid fa-user-graduate text-primary" aria-hidden="true"></i>
                            {{ __('Student Records') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-stone-200/90 bg-[#F8FAF7] px-3 py-1.5 text-xs font-medium text-stone-800 sm:text-sm">
                            <i class="fa-solid fa-money-bill-wave text-secondary" aria-hidden="true"></i>
                            {{ __('Fees & Payments') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-stone-200/90 bg-[#F8FAF7] px-3 py-1.5 text-xs font-medium text-stone-800 sm:text-sm">
                            <i class="fa-solid fa-chart-line text-accent" aria-hidden="true"></i>
                            {{ __('Results & Reports') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="hidden bg-canvas lg:flex lg:w-[48%] lg:shrink-0 lg:items-center lg:justify-center lg:px-6 lg:pb-12 lg:pt-6 xl:px-10 xl:pb-16">
                <x-home-hero-animation />
            </div>
        </main>
    </div>
@endsection
