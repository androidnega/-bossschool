<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Maintenance') }} — BossSchool</title>
    @include('layouts.partials.head-assets')
</head>
<body class="min-h-screen bg-white text-slate-800 antialiased">
    <main class="mx-auto flex min-h-screen max-w-lg flex-col items-center justify-center px-6 py-16 text-center">
        <div class="mb-6 flex size-16 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-2xl text-slate-700">
            <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ __('BossSchool is under maintenance') }}</h1>
        <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ $message }}</p>
        @if (! empty($global))
            <p class="mt-6 text-xs text-slate-500">{{ __('Platform-wide maintenance') }}</p>
        @endif
    </main>
</body>
</html>
