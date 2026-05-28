@php
    $link = 'inline-flex shrink-0 items-center whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium';
    $active = 'bg-page-soft text-primary';
    $idle = 'text-gray-600 hover:bg-page-soft hover:text-primary';
@endphp
<nav class="mb-6 flex flex-nowrap gap-2 overflow-x-auto border-b border-gray-200 pb-4 sm:flex-wrap sm:overflow-visible [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" aria-label="{{ __('Billing') }}">
    <a href="{{ route('billing.index') }}" class="{{ $link }} {{ request()->routeIs('billing.index') ? $active : $idle }}">{{ __('Overview') }}</a>
    <a href="{{ route('billing.plans') }}" class="{{ $link }} {{ request()->routeIs('billing.plans') ? $active : $idle }}">{{ __('Plans') }}</a>
    <a href="{{ route('billing.sms-credits.index') }}" class="{{ $link }} {{ request()->routeIs('billing.sms-credits.*') ? $active : $idle }}">{{ __('SMS credits') }}</a>
    <a href="{{ route('billing.history') }}" class="{{ $link }} {{ request()->routeIs('billing.history') ? $active : $idle }}">{{ __('History') }}</a>
</nav>
