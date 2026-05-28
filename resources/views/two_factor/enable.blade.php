@extends('layouts.app')

@section('title', __('Set up 2FA'))
@section('header-title', __('Set up 2FA'))

@section('content')
    {{-- Errors and session('status') are rendered globally by layouts.app. --}}
    <div class="space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm">
            <h2 class="mb-2 font-semibold">{{ __('Step 1 — Add the account to your authenticator') }}</h2>
            <p class="text-slate-600">{{ __('Open Google Authenticator, Authy, or 1Password and scan the QR code below.') }}</p>

            <div class="mt-4 flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                <div class="flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white p-3"
                     role="img"
                     aria-label="{{ __('Two-factor authentication setup QR code') }}">
                    <div class="size-[220px] [&>svg]:h-full [&>svg]:w-full">
                        {!! $qr_svg !!}
                    </div>
                </div>

                <div class="min-w-0 flex-1 space-y-3">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Manual entry key') }}</p>
                        <p class="mt-1 break-all rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1.5 font-mono text-sm tracking-wider text-slate-800">{{ $secret }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ __("If you can't scan the QR, type this key into your authenticator app.") }}</p>
                    </div>

                    <details class="text-xs">
                        <summary class="cursor-pointer select-none text-slate-500 hover:text-slate-700">{{ __('Show otpauth URI') }}</summary>
                        <p class="mt-2 break-all rounded-md bg-slate-50 p-2 font-mono text-[11px] leading-relaxed text-slate-700">{{ $otpauth_uri }}</p>
                    </details>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm">
            <h2 class="mb-2 font-semibold text-amber-900">{{ __('Step 2 — Save your recovery codes') }}</h2>
            <p class="text-xs text-amber-900">{{ __('Each code can be used ONCE if you lose your authenticator. They will NOT be shown again. Print or store them in a password manager NOW.') }}</p>
            <ul class="mt-2 grid grid-cols-2 gap-1 font-mono text-sm">
                @foreach($recovery_codes as $c)
                    <li>{{ $c }}</li>
                @endforeach
            </ul>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm">
            <h2 class="mb-1 font-semibold">{{ __('Step 3 — Confirm with a 6-digit code') }}</h2>
            <p class="mb-3 text-xs text-slate-500">{{ __('Enter the 6-digit code from your authenticator app. It will submit automatically.') }}</p>

            <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-3" data-otp-form>
                @csrf
                <input type="hidden" name="code" data-otp-value>

                <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                    @for ($i = 1; $i <= 6; $i++)
                        <input type="text"
                               inputmode="numeric"
                               pattern="\d"
                               maxlength="1"
                               autocomplete="one-time-code"
                               data-otp-digit
                               aria-label="{{ __('Digit') }} {{ $i }}"
                               @if($i === 1) autofocus @endif
                               class="size-12 rounded-lg border border-slate-300 bg-white text-center font-mono text-xl font-semibold text-slate-900 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 sm:size-14 sm:text-2xl" />
                    @endfor
                </div>

                <button type="submit" class="rounded-md bg-primary px-3 py-1.5 text-white">{{ __('Confirm and enable') }}</button>
            </form>
        </div>
    </div>

    @include('two_factor.partials.otp-boxes-script')
@endsection
