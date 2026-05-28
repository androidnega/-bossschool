@extends('layouts.guest')

@section('title', 'Sign in — '.config('app.name'))

@section('content')
    <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white p-8 shadow-sm">
        <h1 class="text-xl font-semibold text-primary">Sign in</h1>
        <p class="mt-1 text-sm text-gray-600">Use your school email and password.</p>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <input id="remember" name="remember" type="checkbox" value="1" class="rounded border-gray-300 text-primary focus:ring-primary">
                <label for="remember" class="text-sm text-gray-700">Remember me</label>
            </div>

            <button type="submit"
                class="w-full rounded bg-primary py-2 text-sm font-medium text-white hover:opacity-95">
                Sign in
            </button>
        </form>
    </div>
@endsection
