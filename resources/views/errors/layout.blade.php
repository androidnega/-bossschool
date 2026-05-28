<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? __('Error') }} — {{ config('app.name') }}</title>
    @include('layouts.partials.head-assets')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <main class="mx-auto flex min-h-screen max-w-xl flex-col items-center justify-center px-4 py-10">
        <div class="w-full rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
            <div class="mx-auto mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-2xl font-bold text-rose-600">
                {{ $code ?? '!' }}
            </div>
            <h1 class="text-xl font-semibold">{{ $title ?? __('Something went wrong') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ $body ?? '' }}</p>

            @if(!empty($request_id))
                <p class="mt-3 text-xs text-slate-400">{{ __('Request ID') }}:
                    <code class="rounded bg-slate-100 px-1 py-0.5">{{ $request_id }}</code>
                </p>
            @endif

            <div class="mt-4 flex flex-col items-center gap-2">
                <a href="{{ url('/') }}" class="rounded-md bg-primary px-3 py-1.5 text-sm text-white">{{ __('Go to homepage') }}</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm text-primary hover:underline">{{ __('Back to dashboard') }}</a>
                @endauth
            </div>
        </div>
        <p class="mt-4 text-xs text-slate-400">{{ config('app.name') }}</p>
    </main>
</body>
</html>
