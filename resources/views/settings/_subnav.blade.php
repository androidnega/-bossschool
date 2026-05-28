@php
    $link = 'inline-flex shrink-0 items-center whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium transition-colors';
    $active = 'border border-primary/30 bg-page-soft text-primary';
    $idle = 'border border-transparent text-gray-700 hover:border-gray-200 hover:bg-page-soft hover:text-primary';
@endphp
<nav class="mb-6 flex flex-nowrap gap-2 overflow-x-auto border-b border-gray-200 pb-4 sm:flex-wrap sm:overflow-visible [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" aria-label="{{ __('Settings') }}">
    <a href="{{ route('settings.index') }}" class="{{ $link }} {{ request()->routeIs('settings.index') ? $active : $idle }}">{{ __('School profile') }}</a>
    <a href="{{ route('classes.index') }}" class="{{ $link }} {{ request()->routeIs('classes.*') ? $active : $idle }}">{{ __('Classes') }}</a>
    <a href="{{ route('academic-years.index') }}" class="{{ $link }} {{ request()->routeIs('academic-years.*') ? $active : $idle }}">{{ __('Academic years') }}</a>
    <a href="{{ route('terms.index') }}" class="{{ $link }} {{ request()->routeIs('terms.*') ? $active : $idle }}">{{ __('Terms') }}</a>
</nav>
