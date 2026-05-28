@php
    $rk = $roleKey ?? 'admin';
    $accent = match ($rk) {
        'proprietor' => ['ring' => 'ring-emerald-200/70', 'icon' => 'text-emerald-700', 'active' => 'bg-emerald-50 text-emerald-950 ring-emerald-200/60'],
        'admin' => ['ring' => 'ring-blue-200/70', 'icon' => 'text-blue-700', 'active' => 'bg-blue-50 text-blue-950 ring-blue-200/60'],
        'accountant' => ['ring' => 'ring-amber-200/70', 'icon' => 'text-amber-700', 'active' => 'bg-emerald-50 text-emerald-950 ring-amber-200/50'],
        'teacher' => ['ring' => 'ring-indigo-200/70', 'icon' => 'text-indigo-700', 'active' => 'bg-indigo-50 text-indigo-950 ring-indigo-200/60'],
        default => ['ring' => 'ring-stone-200/80', 'icon' => 'text-stone-600', 'active' => 'bg-stone-200/70 text-stone-900 ring-stone-300/40'],
    };
    $navBase = 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-stone-600 transition-colors hover:bg-stone-200/60 hover:text-stone-900';
    $navActive = 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold ring-1 '.$accent['active'];
@endphp

<nav class="flex flex-1 flex-col gap-0.5 overflow-y-auto overscroll-contain p-3 text-sm" aria-label="{{ __('Main navigation') }}">
    @if($rk === 'proprietor')
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? $navActive : $navBase }}">
            <i class="fa-solid fa-gauge-high w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Dashboard') }}</span>
        </a>
        <a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') && ! request()->routeIs('students.report-card') ? $navActive : $navBase }}">
            <i class="fa-solid fa-user-graduate w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Students') }}</span>
        </a>
        <a href="{{ route('fees.index') }}" class="{{ request()->routeIs('fees.*', 'payments.*', 'debtors.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-money-bill-wave w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Fees') }}</span>
        </a>
        <a href="{{ route('results.index') }}" class="{{ request()->routeIs('subjects.*', 'results.*', 'students.report-card') ? $navActive : $navBase }}">
            <i class="fa-solid fa-clipboard-list w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Results') }}</span>
        </a>
        <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-user-check w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Attendance') }}</span>
        </a>
        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-chart-pie w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Reports') }}</span>
        </a>
        <a href="{{ route('promotions.index') }}" class="{{ request()->routeIs('promotions.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-arrow-up-right-dots w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Promotion') }}</span>
        </a>
        <a href="{{ route('report-card-meta.index') }}" class="{{ request()->routeIs('report-card-meta.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-file-pen w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Term sheet') }}</span>
        </a>
        @can('billing.view')
            <a href="{{ route('billing.index') }}" class="{{ request()->routeIs('billing.*') ? $navActive : $navBase }}">
                <i class="fa-solid fa-credit-card w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
                <span>{{ __('Billing') }}</span>
            </a>
        @endcan
        @can('settings.manage')
            <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*', 'classes.*', 'terms.*') ? $navActive : $navBase }}">
                <i class="fa-solid fa-gear w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
                <span>{{ __('Settings') }}</span>
            </a>
        @endcan
        @can('viewAny', \App\Models\Message::class)
            <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? $navActive : $navBase }}">
                <i class="fa-solid fa-bullhorn w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
                <span>{{ __('Notices') }}</span>
            </a>
        @endcan
    @elseif($rk === 'admin')
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? $navActive : $navBase }}">
            <i class="fa-solid fa-gauge-high w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Dashboard') }}</span>
        </a>
        <a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') && ! request()->routeIs('students.report-card') ? $navActive : $navBase }}">
            <i class="fa-solid fa-user-graduate w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Students') }}</span>
        </a>
        <a href="{{ route('classes.index') }}" class="{{ request()->routeIs('classes.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-users-between-lines w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Classes') }}</span>
        </a>
        <a href="{{ route('staff.index') }}" class="{{ request()->routeIs('staff.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-id-badge w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Staff') }}</span>
        </a>
        <a href="{{ route('results.index') }}" class="{{ request()->routeIs('subjects.*', 'results.*', 'students.report-card') ? $navActive : $navBase }}">
            <i class="fa-solid fa-clipboard-list w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Results') }}</span>
        </a>
        <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-user-check w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Attendance') }}</span>
        </a>
        <a href="{{ route('promotions.index') }}" class="{{ request()->routeIs('promotions.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-arrow-up-right-dots w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Promotion') }}</span>
        </a>
        <a href="{{ route('report-card-meta.index') }}" class="{{ request()->routeIs('report-card-meta.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-file-pen w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Term sheet') }}</span>
        </a>
        @can('viewAny', \App\Models\Message::class)
            <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? $navActive : $navBase }}">
                <i class="fa-solid fa-bullhorn w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
                <span>{{ __('Notices') }}</span>
            </a>
        @endcan
        @can('settings.manage')
            <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*', 'terms.*', 'academic-years.*') ? $navActive : $navBase }}">
                <i class="fa-solid fa-gear w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
                <span>{{ __('Settings') }}</span>
            </a>
        @endcan
    @elseif($rk === 'accountant')
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? $navActive : $navBase }}">
            <i class="fa-solid fa-gauge-high w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Dashboard') }}</span>
        </a>
        <a href="{{ route('fees.index') }}" class="{{ request()->routeIs('fees.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-money-bill-wave w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Fees') }}</span>
        </a>
        <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-receipt w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Payments') }}</span>
        </a>
        <a href="{{ route('debtors.index') }}" class="{{ request()->routeIs('debtors.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-user-clock w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Debtors') }}</span>
        </a>
        <a href="{{ route('reports.finance') }}" class="{{ request()->routeIs('reports.finance') ? $navActive : $navBase }}">
            <i class="fa-solid fa-chart-line w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Finance reports') }}</span>
        </a>
        @can('viewAny', \App\Models\Message::class)
            <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? $navActive : $navBase }}">
                <i class="fa-solid fa-bullhorn w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
                <span>{{ __('Notices') }}</span>
            </a>
        @endcan
    @elseif($rk === 'teacher')
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? $navActive : $navBase }}">
            <i class="fa-solid fa-gauge-high w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Dashboard') }}</span>
        </a>
        <a href="{{ route('classes.index') }}" class="{{ request()->routeIs('classes.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-chalkboard-user w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('My classes') }}</span>
        </a>
        <a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') && ! request()->routeIs('students.report-card') ? $navActive : $navBase }}">
            <i class="fa-solid fa-user-graduate w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Students') }}</span>
        </a>
        <a href="{{ route('results.index') }}" class="{{ request()->routeIs('results.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-pen-to-square w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Results') }}</span>
        </a>
        <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-user-check w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Attendance') }}</span>
        </a>
        <a href="{{ route('subjects.index') }}" class="{{ request()->routeIs('subjects.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-book w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Subjects') }}</span>
        </a>
        <a href="{{ route('report-card-meta.index') }}" class="{{ request()->routeIs('report-card-meta.*') ? $navActive : $navBase }}">
            <i class="fa-solid fa-file-pen w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
            <span>{{ __('Term sheet') }}</span>
        </a>
        @can('viewAny', \App\Models\Message::class)
            <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? $navActive : $navBase }}">
                <i class="fa-solid fa-bullhorn w-5 shrink-0 text-center text-[0.95rem] opacity-90" aria-hidden="true"></i>
                <span>{{ __('Notices') }}</span>
            </a>
        @endcan
    @endif
</nav>
