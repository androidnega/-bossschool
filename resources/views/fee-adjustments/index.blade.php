@extends('layouts.app')

@section('title', __('Fee adjustments'))

@section('header-title', 'Fees')

@section('content')
    @include('finances._subnav')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-primary">{{ __('Discounts, scholarships & waivers') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Adjustment requests must be approved before they reduce an invoice balance.') }}</p>
        </div>
        @can('create', \App\Models\FeeAdjustment::class)
            <a href="{{ route('fee-adjustments.create') }}" class="inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Request adjustment') }}</a>
        @endcan
    </div>

    @if(session('status'))
        <div class="mt-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="GET" class="mt-4 flex flex-wrap gap-2 text-sm">
        <select name="status" class="rounded-md border border-gray-300 bg-page px-2 py-1.5">
            <option value="">{{ __('Any status') }}</option>
            @foreach (\App\Models\FeeAdjustment::STATUSES as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords($s) }}</option>
            @endforeach
        </select>
        <button class="rounded-md border border-gray-300 bg-page px-3 py-1.5">{{ __('Filter') }}</button>
    </form>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-page">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
            <thead class="bg-page-soft">
                <tr>
                    <th class="px-4 py-3">{{ __('Student') }}</th>
                    <th class="px-4 py-3">{{ __('Type') }}</th>
                    <th class="px-4 py-3">{{ __('Description') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Amount') }}</th>
                    <th class="px-4 py-3">{{ __('Invoice') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($adjustments as $a)
                    <tr>
                        <td class="px-4 py-3">{{ $a->student?->name }}</td>
                        <td class="px-4 py-3 capitalize">{{ $a->type }}</td>
                        <td class="px-4 py-3">{{ $a->description }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ cedis($a->amount) }}</td>
                        <td class="px-4 py-3">@if($a->invoice)<a href="{{ route('fee-invoices.show', $a->invoice) }}" class="font-mono text-primary hover:underline">{{ $a->invoice->invoice_number }}</a>@endif</td>
                        <td class="px-4 py-3"><span class="inline-flex rounded-full bg-page-soft px-2 py-0.5 text-xs font-medium capitalize">{{ $a->status }}</span></td>
                        <td class="px-4 py-3 text-right space-x-2 text-xs">
                            @can('decide', $a)
                                @if($a->status === \App\Models\FeeAdjustment::STATUS_PENDING)
                                    <form method="POST" action="{{ route('fee-adjustments.approve', $a) }}" class="inline">@csrf<button class="text-emerald-700 hover:underline">{{ __('Approve') }}</button></form>
                                    <form method="POST" action="{{ route('fee-adjustments.reject', $a) }}" class="inline" onsubmit="this.notes.value = prompt({{ json_encode(__('Reason for rejection?')) }}, ''); return !!this.notes.value;">@csrf<input type="hidden" name="notes"><button class="text-red-700 hover:underline">{{ __('Reject') }}</button></form>
                                @endif
                            @endcan
                            @can('cancel', $a)
                                <form method="POST" action="{{ route('fee-adjustments.destroy', $a) }}" class="inline" onsubmit="return confirm({{ json_encode(__('Cancel this adjustment?')) }})">@csrf @method('DELETE')<button class="text-gray-600 hover:underline">{{ __('Cancel') }}</button></form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">{{ __('No adjustment requests.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($adjustments->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $adjustments->links() }}</div>
        @endif
    </div>
@endsection
