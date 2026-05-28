@php
    // Sidebar selection is driven by the signed-in user's role, not by the
    // current route name. Previously this only flipped to nav-platform when
    // the route name started with "platform.*" — which meant SuperAdmin on
    // /dashboard (route name: "dashboard") fell through and got an empty
    // sidebar. Now: role decides which nav to render; route name only
    // decides the small context label under the brand.
    $user = auth()->user();
    $isPlatform = $user && $user->isSuperAdmin();
    $isParentPortal = request()->routeIs('portal.parent.*');
    $isStudentPortal = request()->routeIs('portal.student.*');
    $contextLabel = $isPlatform
        ? __('Platform')
        : ($isParentPortal
            ? __('Parent portal')
            : ($isStudentPortal
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
@elseif($isParentPortal)
    @include('layouts.partials.nav-parent')
@elseif($isStudentPortal)
    @include('layouts.partials.nav-student')
@else
    @include('layouts.partials.nav-school', ['roleKey' => $user->sidebarKey()])
@endif
