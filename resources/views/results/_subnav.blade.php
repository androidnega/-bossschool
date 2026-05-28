@php
    $link = 'inline-flex shrink-0 items-center whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium';
    $active = 'bg-page-soft text-primary';
    $idle = 'text-gray-600 hover:bg-page-soft hover:text-primary';
@endphp
<nav class="mb-6 flex flex-nowrap gap-2 overflow-x-auto border-b border-gray-200 pb-4 sm:flex-wrap sm:overflow-visible [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" aria-label="{{ __('Results section') }}">
    <a href="{{ route('subjects.index') }}" class="{{ $link }} {{ request()->routeIs('subjects.*') ? $active : $idle }}">{{ __('Subjects') }}</a>
    <a href="{{ route('results.index') }}" class="{{ $link }} {{ request()->routeIs('results.*') ? $active : $idle }}">{{ __('Results') }}</a>
</nav>
