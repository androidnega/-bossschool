@extends('layouts.guest')

@section('title', __('Create SuperAdmin account'))

@section('content')
    <div class="mx-auto w-full min-w-0 max-w-[26rem] overflow-x-hidden">
        <div class="login-card login-card-pull-in rounded-2xl border border-stone-200/90 bg-white px-5 py-5 sm:px-6 sm:py-5">
            <h1 class="text-center text-lg font-semibold tracking-tight text-stone-900">{{ __('First-run setup') }}</h1>
            <p class="mt-1 text-center text-xs leading-snug text-stone-500">
                {{ __('Create the platform SuperAdmin account. This page locks itself the moment a SuperAdmin exists.') }}
            </p>

            @if (session('status'))
                <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('superadmin.setup.store') }}" class="mt-5 space-y-3.5">
                @csrf

                <div>
                    <label for="name" class="mb-1 block text-xs font-medium text-stone-700">{{ __('Full name') }}</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                        placeholder="{{ __('Example: Ama Mensah') }}"
                        class="block h-9 w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-900 transition focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-xs font-medium text-stone-700">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                        placeholder="{{ __('Example: superadmin@bossschool.com') }}"
                        class="block h-9 w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-900 transition focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 @error('email') border-red-400 @enderror">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="mb-1 block text-xs font-medium text-stone-700">{{ __('Password') }}</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                            class="block h-9 w-full rounded-lg border border-stone-200 bg-white px-3 py-2 pr-9 text-sm text-stone-900 transition focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 @error('password') border-red-400 @enderror">
                        <button type="button" data-pw-toggle="password" aria-label="{{ __('Show password') }}" aria-pressed="false"
                            class="absolute inset-y-0 right-0 flex w-9 items-center justify-center text-stone-400 transition hover:text-stone-600 focus:text-primary focus:outline-none">
                            <i class="fa-solid fa-eye text-xs" data-pw-icon aria-hidden="true"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-[11px] text-stone-500">{{ __('At least 12 characters with mixed case and a number.') }}</p>
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-xs font-medium text-stone-700">{{ __('Confirm password') }}</label>
                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                            class="block h-9 w-full rounded-lg border border-stone-200 bg-white px-3 py-2 pr-9 text-sm text-stone-900 transition focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                        <button type="button" data-pw-toggle="password_confirmation" aria-label="{{ __('Show password') }}" aria-pressed="false"
                            class="absolute inset-y-0 right-0 flex w-9 items-center justify-center text-stone-400 transition hover:text-stone-600 focus:text-primary focus:outline-none">
                            <i class="fa-solid fa-eye text-xs" data-pw-icon aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="mt-2 h-9 w-full rounded-lg bg-primary text-sm font-semibold text-white transition hover:bg-primary/95 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:ring-offset-1 focus:ring-offset-white">
                    {{ __('Create SuperAdmin') }}
                </button>

                <p class="mt-3 text-center text-xs text-stone-500">
                    <a href="{{ route('login') }}" class="font-medium text-primary hover:text-primary/90">{{ __('Back to sign in') }}</a>
                </p>
            </form>
        </div>
    </div>
@endsection
