@extends('layouts.guest')

@section('title', __('Two-factor challenge'))

@section('content')
    <div class="mx-auto mt-12 max-w-md rounded-xl border border-slate-200 bg-white p-5 text-sm">
        <h1 class="mb-3 text-lg font-semibold">{{ __('Two-factor verification') }}</h1>
        @if($errors->any())
            <div class="mb-3 rounded-md border border-rose-200 bg-rose-50 p-2 text-rose-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('two-factor.challenge.attempt') }}" class="space-y-4" data-otp-form>
            @csrf
            <input type="hidden" name="code" data-otp-value>

            <div>
                <label class="text-xs text-slate-500">{{ __('6-digit code from your authenticator app') }}</label>
                <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                    @for ($i = 1; $i <= 6; $i++)
                        <input type="text"
                               inputmode="numeric"
                               pattern="\d"
                               maxlength="1"
                               autocomplete="one-time-code"
                               data-otp-digit
                               aria-label="{{ __('Digit') }} {{ $i }}"
                               @if($i === 1) autofocus @endif
                               class="size-11 rounded-lg border border-slate-300 bg-white text-center font-mono text-lg font-semibold text-slate-900 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 sm:size-12 sm:text-xl" />
                    @endfor
                </div>
            </div>

            <details class="text-xs">
                <summary class="cursor-pointer">{{ __('Use a recovery code instead') }}</summary>
                <input type="text" name="recovery_code" class="mt-2 w-full rounded-md border border-slate-300 px-2 py-1.5 font-mono" placeholder="ABC12-XYZ34" />
            </details>

            <button type="submit" class="w-full rounded-md bg-primary px-3 py-2 text-white">{{ __('Verify') }}</button>
        </form>
    </div>

    @include('two_factor.partials.otp-boxes-script')
@endsection
