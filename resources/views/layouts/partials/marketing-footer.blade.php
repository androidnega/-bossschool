<footer class="relative z-10 border-t border-slate-200/60 bg-white/60 backdrop-blur">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-4 text-[11px] font-medium text-slate-500 sm:flex-row sm:px-6 sm:text-xs lg:px-8">
        <p>&copy; {{ now()->year }} BossSchool · {{ __('Takoradi, Ghana') }}</p>
        <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5">
            <a href="{{ route('about') }}" class="transition-colors hover:text-blue-600">{{ __('About') }}</a>
            <a href="{{ route('contact') }}" class="transition-colors hover:text-blue-600">{{ __('Contact') }}</a>
            <a href="mailto:hello@bossschoolapp.com" class="transition-colors hover:text-blue-600">hello@bossschoolapp.com</a>
            @guest
                <a href="{{ route('login') }}" class="font-semibold transition-colors hover:text-blue-600">{{ __('Sign in') }}</a>
            @endguest
        </div>
    </div>
</footer>
