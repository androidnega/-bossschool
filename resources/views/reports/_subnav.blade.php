@php
    $link = 'inline-flex shrink-0 items-center whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium';
    $active = 'bg-page-soft text-primary';
    $idle = 'text-gray-600 hover:bg-page-soft hover:text-primary';
@endphp
<nav class="mb-6 flex flex-nowrap gap-2 overflow-x-auto border-b border-gray-200 pb-4 sm:flex-wrap sm:overflow-visible [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" aria-label="{{ __('Reports') }}">
    @can('reports.overview')
        <a href="{{ route('reports.index') }}" class="{{ $link }} {{ request()->routeIs('reports.index') ? $active : $idle }}">{{ __('Overview') }}</a>
    @endcan
    @can('reports.finance')
        <a href="{{ route('reports.finance') }}" class="{{ $link }} {{ request()->routeIs('reports.finance') ? $active : $idle }}">{{ __('Finance') }}</a>
    @endcan
    @can('reports.students')
        <a href="{{ route('reports.students') }}" class="{{ $link }} {{ request()->routeIs('reports.students') ? $active : $idle }}">{{ __('Students') }}</a>
    @endcan
    @can('reports.academic')
        <a href="{{ route('reports.academic') }}" class="{{ $link }} {{ request()->routeIs('reports.academic') ? $active : $idle }}">{{ __('Academic') }}</a>
    @endcan
</nav>
