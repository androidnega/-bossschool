@extends('layouts.app')

@section('title', __('Two-factor authentication'))
@section('header-title', __('Two-factor authentication'))

@section('content')
    {{-- Errors and session('status') are rendered globally by layouts.app. --}}
    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm">
        @if($enabled)
            <p class="mb-3 text-emerald-700">{{ __('Two-factor authentication is ENABLED on this account.') }}</p>
            <form method="POST" action="{{ route('two-factor.disable') }}" class="space-y-2">
                @csrf
                <input type="password" name="password" required placeholder="{{ __('Confirm your password to disable') }}"
                    class="w-full rounded-md border border-slate-300 px-2 py-1.5" />
                <button type="submit" class="rounded-md bg-rose-600 px-3 py-1.5 text-white">{{ __('Disable 2FA') }}</button>
            </form>
        @else
            @if($required)
                <p class="mb-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-amber-900">
                    {{ __('Two-factor authentication is REQUIRED for your role. Please set it up to continue.') }}
                </p>
            @else
                <p class="mb-3 text-slate-600">{{ __('Add a second factor (an authenticator app) for stronger account protection.') }}</p>
            @endif
            <form method="POST" action="{{ route('two-factor.enable') }}">
                @csrf
                <button type="submit" class="rounded-md bg-primary px-3 py-1.5 text-white">{{ __('Enable two-factor authentication') }}</button>
            </form>
        @endif
    </div>
@endsection
