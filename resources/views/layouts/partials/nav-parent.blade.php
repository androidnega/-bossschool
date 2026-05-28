@php
    $navBase = 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-emerald-900/70 transition-colors hover:bg-emerald-50 hover:text-emerald-950';
    $navActive = 'flex items-center gap-3 rounded-lg bg-emerald-50 px-3 py-2.5 text-sm font-semibold text-emerald-950 ring-1 ring-emerald-200/70';
@endphp

<nav class="flex flex-1 flex-col gap-0.5 overflow-y-auto overscroll-contain p-3 text-sm" aria-label="{{ __('Parent navigation') }}">
    <a href="{{ route('portal.parent.index') }}" class="{{ request()->routeIs('portal.parent.index') ? $navActive : $navBase }}">
        <i class="fa-solid fa-house w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
        <span>{{ __('Dashboard') }}</span>
    </a>
    <span class="px-3 pt-2 text-xs font-semibold uppercase tracking-wide text-emerald-800/60">{{ __('My children') }}</span>
    @php
        $currentStudent = request()->route('student');
    @endphp
    @foreach(auth()->user()->children()->orderBy('name')->get() as $child)
        <a href="{{ route('portal.parent.child', $child) }}" class="{{ $currentStudent && $currentStudent->is($child) ? $navActive : $navBase }}">
            <i class="fa-solid fa-child w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span class="truncate">{{ $child->name }}</span>
        </a>
    @endforeach
</nav>
