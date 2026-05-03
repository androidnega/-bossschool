<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')BoSchool | @yield('title')@else BoSchool @endif</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-[100dvh] overflow-hidden bg-stone-100 font-sans text-gray-900 antialiased">
    <div class="flex h-full overflow-hidden">
        <input type="checkbox" id="mobile-sidebar" class="peer sr-only" aria-label="{{ __('Toggle navigation') }}">

        <label for="mobile-sidebar" class="fixed inset-0 z-30 hidden bg-stone-900/25 peer-checked:block lg:hidden" aria-hidden="true"></label>

        <aside class="fixed inset-y-0 left-0 z-40 flex w-[min(17rem,88vw)] max-w-[17rem] shrink-0 -translate-x-full flex-col border-r border-stone-200/90 bg-stone-50 transition-transform duration-200 ease-out peer-checked:translate-x-0 lg:static lg:z-0 lg:translate-x-0 lg:shrink-0">
            @include('layouts.partials.app-sidebar')
        </aside>

        <div class="flex min-h-0 min-w-0 flex-1 flex-col bg-white">
            <header class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-b border-stone-200/90 bg-white px-3 py-2.5 sm:px-4 lg:px-6">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <label for="mobile-sidebar" class="inline-flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-stone-200 bg-white text-stone-600 shadow-sm transition hover:bg-stone-50 lg:hidden">
                        <span class="sr-only">{{ __('Open menu') }}</span>
                        <i class="fa-solid fa-bars text-lg" aria-hidden="true"></i>
                    </label>
                    <span class="hidden min-w-0 truncate text-sm font-semibold text-stone-800 lg:inline">@yield('header-title', '')</span>
                </div>
                <div class="flex shrink-0 items-center gap-2 text-sm sm:gap-3">
                    <span class="hidden max-w-[12rem] items-center gap-2 truncate text-stone-700 sm:flex">
                        <i class="fa-solid fa-circle-user text-lg text-stone-400" aria-hidden="true"></i>
                        <span class="truncate font-medium">{{ auth()->user()->name }}</span>
                    </span>
                    <span class="max-w-[9rem] truncate font-medium text-stone-700 sm:hidden">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-1.5 text-sm font-medium text-stone-600 shadow-sm transition hover:bg-stone-50 hover:text-stone-900">
                            <i class="fa-solid fa-right-from-bracket text-stone-400" aria-hidden="true"></i>
                            <span class="hidden sm:inline">{{ __('Log out') }}</span>
                        </button>
                    </form>
                </div>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain bg-white">
                <div class="p-4 sm:p-6 lg:p-8">
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
            </div>
        </div>
    </div>
</body>
</html>
