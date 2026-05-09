@php
    $user = auth()->user();
    $name = trim((string) $user->name);
    $email = (string) ($user->email ?? '');
    $words = preg_split('/\s+/u', $name) ?: [];
    $initials = collect($words)
        ->filter()
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr((string) $w, 0, 1)))
        ->implode('');
    if ($initials === '' && $email !== '') {
        $initials = mb_strtoupper(mb_substr($email, 0, 2));
    }
    if ($initials === '') {
        $initials = 'U';
    }

    $roleLabel = match ((string) $user->role) {
        'SuperAdmin' => __('Platform Super Admin'),
        'Proprietor' => __('Proprietor'),
        'Admin' => __('School Admin'),
        'Accountant' => __('Accountant'),
        'Teacher' => __('Teacher'),
        'Parent' => __('Parent'),
        'Student' => __('Student'),
        default => __('Member'),
    };

    $settingsUrl = null;
    if ($user->isSuperAdmin()) {
        $settingsUrl = route('platform.settings.index');
    } elseif ($user->isSchoolStaff()) {
        $settingsUrl = route('settings.index');
    }
@endphp

<details class="relative" data-dropdown>
    <summary
        class="inline-flex cursor-pointer list-none items-center gap-2 rounded-full border border-stone-200 bg-white py-1 pl-1 pr-2 text-sm font-medium text-stone-700 transition hover:border-stone-300 hover:bg-stone-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/35 focus-visible:ring-offset-2 sm:pr-3 [&::-webkit-details-marker]:hidden"
        aria-haspopup="menu"
        aria-label="{{ __('Account menu') }}">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold uppercase tracking-wide text-white" aria-hidden="true">{{ $initials }}</span>
        <span class="hidden max-w-[8rem] truncate text-stone-700 sm:inline">{{ $name }}</span>
        <i class="fa-solid fa-chevron-down text-[10px] text-stone-400" aria-hidden="true"></i>
    </summary>

    <div
        class="absolute right-0 z-40 mt-2 w-60 origin-top-right rounded-xl border border-stone-200 bg-white p-1.5 ring-1 ring-stone-900/5"
        role="menu"
        aria-label="{{ __('Account menu') }}">
        <div class="flex items-start gap-2.5 rounded-lg px-2.5 py-2">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold uppercase tracking-wide text-white" aria-hidden="true">{{ $initials }}</span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-stone-900">{{ $name }}</p>
                @if ($email !== '')
                    <p class="truncate text-xs text-stone-500">{{ $email }}</p>
                @endif
                <p class="mt-0.5 inline-flex items-center gap-1 text-[0.65rem] font-semibold uppercase tracking-wide text-stone-500">
                    <span class="size-1 rounded-full bg-primary" aria-hidden="true"></span>
                    {{ $roleLabel }}
                </p>
            </div>
        </div>

        <div class="my-1 border-t border-stone-200/80"></div>

        <span
            class="flex cursor-not-allowed items-center justify-between gap-2 rounded-lg px-2.5 py-2 text-sm text-stone-500"
            role="menuitem"
            aria-disabled="true"
            title="{{ __('Coming soon') }}">
            <span class="flex items-center gap-2.5">
                <i class="fa-solid fa-id-badge w-4 text-center text-stone-400" aria-hidden="true"></i>
                <span>{{ __('Profile') }}</span>
            </span>
            <span class="rounded-full bg-stone-100 px-1.5 py-0.5 text-[0.6rem] font-semibold uppercase tracking-wide text-stone-500">{{ __('Soon') }}</span>
        </span>

        @if ($settingsUrl)
            <a
                href="{{ $settingsUrl }}"
                class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm text-stone-700 transition hover:bg-stone-100"
                role="menuitem">
                <i class="fa-solid fa-sliders w-4 text-center text-stone-400" aria-hidden="true"></i>
                <span>{{ __('Settings') }}</span>
            </a>
        @else
            <span
                class="flex cursor-not-allowed items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm text-stone-400"
                role="menuitem"
                aria-disabled="true"
                title="{{ __('No settings for this role') }}">
                <i class="fa-solid fa-sliders w-4 text-center text-stone-300" aria-hidden="true"></i>
                <span>{{ __('Settings') }}</span>
            </span>
        @endif

        <div class="my-1 border-t border-stone-200/80"></div>

        <form method="POST" action="{{ route('logout') }}" role="none">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-sm text-rose-700 transition hover:bg-rose-50"
                role="menuitem">
                <i class="fa-solid fa-right-from-bracket w-4 text-center text-rose-500" aria-hidden="true"></i>
                <span>{{ __('Log out') }}</span>
            </button>
        </form>
    </div>
</details>
