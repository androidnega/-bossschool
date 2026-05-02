<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-page font-sans text-gray-900 antialiased">
    <header class="border-b border-gray-200 bg-white">
        <div class="flex h-14 items-center justify-between px-4 lg:px-8">
            <div class="flex items-center gap-3">
                <span class="text-lg font-semibold text-primary">{{ config('app.name') }}</span>
            </div>
            <div class="flex items-center gap-4 text-sm text-gray-700">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded border border-secondary px-3 py-1 text-secondary hover:bg-gray-50">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-3.5rem)]">
        <aside class="hidden w-52 shrink-0 border-r border-gray-200 bg-white lg:block">
            <nav class="flex flex-col gap-1 p-4 text-sm">
                <a href="{{ route('dashboard') }}" class="rounded px-3 py-2 font-medium text-primary hover:bg-gray-50">Dashboard</a>
                <span class="rounded px-3 py-2 text-gray-400">Students</span>
                <span class="rounded px-3 py-2 text-gray-400">Fees</span>
                <span class="rounded px-3 py-2 text-gray-400">Results</span>
                <span class="rounded px-3 py-2 text-gray-400">Reports</span>
            </nav>
        </aside>

        <div class="flex-1 overflow-auto">
            <div class="border-b border-gray-200 bg-white px-4 py-3 lg:hidden">
                <nav class="flex flex-wrap gap-2 text-xs">
                    <a href="{{ route('dashboard') }}" class="text-primary">Dashboard</a>
                    <span class="text-gray-400">Students</span>
                    <span class="text-gray-400">Fees</span>
                    <span class="text-gray-400">Results</span>
                    <span class="text-gray-400">Reports</span>
                </nav>
            </div>
            <div class="p-4 lg:p-8">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
