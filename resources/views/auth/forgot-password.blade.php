@extends('layouts.guest')

@section('title', __('Forgot password'))

@section('content')
    <div class="login-page mx-auto w-full min-w-0 max-w-[22rem] overflow-x-hidden">
        <div class="login-card login-card-pull-in rounded-2xl border border-stone-200/90 bg-white px-5 py-5 sm:px-6 sm:py-5">
            <h1 class="text-center text-lg font-semibold tracking-tight text-stone-900">{{ __('Forgot your password?') }}</h1>
            <p class="mt-1 text-center text-xs leading-snug text-stone-500">{{ __('Enter your email and we will send you a reset link.') }}</p>

            @if (session('status'))
                <p class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-3.5">
                @csrf

                <div>
                    <label for="email" class="mb-1 block text-xs font-medium text-stone-700">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        class="block h-9 w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-900 transition focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 @error('email') border-red-400 focus:border-red-500 focus:ring-red-200 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="mt-2 h-9 w-full rounded-lg bg-primary text-sm font-semibold text-white transition hover:bg-primary/95 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:ring-offset-1 focus:ring-offset-white">
                    {{ __('Send reset link') }}
                </button>

                <p class="mt-3 text-center text-xs text-stone-500">
                    <a href="{{ route('login') }}" class="font-medium text-primary hover:text-primary/90">{{ __('Back to sign in') }}</a>
                </p>
            </form>
        </div>
    </div>
@endsection
