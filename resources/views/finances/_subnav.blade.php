@php
    $link = 'inline-flex shrink-0 items-center whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium';
    $active = 'bg-page-soft text-primary';
    $idle = 'text-gray-600 hover:bg-page-soft hover:text-primary';
@endphp
<nav class="mb-6 flex flex-nowrap gap-2 overflow-x-auto border-b border-gray-200 pb-4 sm:flex-wrap sm:overflow-visible [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" aria-label="{{ __('Fees section') }}">
    <a href="{{ route('fees.index') }}" class="{{ $link }} {{ request()->routeIs('fees.*') ? $active : $idle }}">{{ __('Fee structure') }}</a>
    <a href="{{ route('fee-invoices.index') }}" class="{{ $link }} {{ request()->routeIs('fee-invoices.*') ? $active : $idle }}">{{ __('Invoices') }}</a>
    <a href="{{ route('payments.index') }}" class="{{ $link }} {{ request()->routeIs('payments.*') ? $active : $idle }}">{{ __('Payments') }}</a>
    <a href="{{ route('fee-adjustments.index') }}" class="{{ $link }} {{ request()->routeIs('fee-adjustments.*') ? $active : $idle }}">{{ __('Adjustments') }}</a>
    <a href="{{ route('debtors.index') }}" class="{{ $link }} {{ request()->routeIs('debtors.*') ? $active : $idle }}">{{ __('Debtors') }}</a>
</nav>
