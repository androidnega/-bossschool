@php
    $isPlatform = auth()->user()->isSuperAdmin() && request()->routeIs('platform.*');
    $contextLabel = $isPlatform
        ? __('Platform')
        : (request()->routeIs('portal.parent.*')
            ? __('Parent portal')
            : (request()->routeIs('portal.student.*')
                ? __('Student portal')
                : null));
@endphp

<div class="flex items-center gap-2.5 border-b border-stone-200/80 px-4 py-4">
    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary text-base font-bold text-white" aria-hidden="true">B</span>
    <div class="min-w-0 flex-1">
        <div class="truncate text-base font-semibold tracking-tight text-stone-800">BossSchool</div>
        @if ($contextLabel)
            <div class="truncate text-xs font-medium text-stone-500">{{ $contextLabel }}</div>
        @endif
    </div>
</div>

@if ($isPlatform)
    @include('layouts.partials.nav-platform')
@elseif(request()->routeIs('portal.parent.*'))
    @include('layouts.partials.nav-parent')
@elseif(request()->routeIs('portal.student.*'))
    @include('layouts.partials.nav-student')
@else
    @include('layouts.partials.nav-school', ['roleKey' => auth()->user()->sidebarKey()])
@endif
