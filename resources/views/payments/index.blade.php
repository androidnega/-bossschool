@extends('layouts.app')

@section('title', __('Payments'))

@section('header-title', 'Fees')

@section('content')
    @include('finances._subnav')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-primary">{{ __('Payments') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Payment history and receipts.') }}</p>
        </div>
        @can('create', \App\Models\Payment::class)
            <a href="{{ route('payments.create') }}" class="inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Record payment') }}</a>
        @endcan
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-page">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-page-soft">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Date') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Student') }}</th>
                        <th class="hidden px-4 py-3 font-medium text-gray-700 md:table-cell">{{ __('Method') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Amount') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Receipt') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-page">
                    @forelse ($payments as $payment)
                        <tr class="hover:bg-page-soft/80">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-900">{{ $payment->date?->format('M j, Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-900">{{ $payment->student?->name }}</span>
                                <div class="text-xs text-gray-500 md:hidden">{{ match($payment->method) { 'momo' => 'MoMo', 'cash' => __('Cash'), 'bank' => __('Bank'), default => $payment->method } }}</div>
                            </td>
                            <td class="hidden px-4 py-3 text-gray-700 md:table-cell">{{ match($payment->method) { 'momo' => 'MoMo', 'cash' => __('Cash'), 'bank' => __('Bank'), default => $payment->method } }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ cedis($payment->amount) }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('payments.show', $payment) }}" class="font-mono text-sm text-primary hover:underline">{{ $payment->receipt_id }}</a>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('payments.show', $payment) }}" class="text-primary hover:underline">{{ __('View') }}</a>
                                @can('delete', $payment)
                                    <span class="text-gray-300">·</span>
                                    <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="inline" onsubmit="return confirm({{ json_encode(__('Remove this payment?')) }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-600">{{ __('No payments yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $payments->links() }}</div>
        @endif
    </div>
@endsection
