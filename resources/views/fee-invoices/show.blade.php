@extends('layouts.app')

@section('title', __('Invoice :n', ['n' => $invoice->invoice_number]))

@section('header-title', 'Fees')

@section('content')
    @include('finances._subnav')

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('fee-invoices.index') }}" class="text-sm font-medium text-secondary hover:text-primary">← {{ __('Back to invoices') }}</a>
            <h1 class="mt-2 text-2xl font-semibold text-primary">{{ __('Invoice') }} <span class="font-mono">{{ $invoice->invoice_number }}</span></h1>
            <p class="mt-1 text-sm text-gray-600">{{ $invoice->student?->name }} · {{ $invoice->term?->name ?? '—' }} · {{ $invoice->academicYear?->name ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-2 text-sm">
            <a href="{{ route('fee-invoices.pdf', $invoice) }}" class="rounded-md border border-gray-300 bg-page px-3 py-1.5 text-gray-700 hover:bg-page-soft">{{ __('Download PDF') }}</a>
            @if(in_array($invoice->status, [\App\Models\FeeInvoice::STATUS_ISSUED, \App\Models\FeeInvoice::STATUS_PARTIALLY_PAID]))
                @can('initiate', [\App\Models\PaymentTransaction::class, $invoice])
                    @php
                        $registry = app(\App\Services\Payments\PaymentProviderRegistry::class);
                        $enabledProviders = $registry->enabledKeys();
                    @endphp
                    @if(! empty($enabledProviders))
                        <form method="POST" action="{{ route('fee-invoices.pay-online', $invoice) }}" class="inline-flex items-center gap-2">
                            @csrf
                            <select name="provider" class="rounded-md border border-gray-300 px-2 py-1.5 text-sm">
                                @foreach($enabledProviders as $key)
                                    <option value="{{ $key }}">{{ ucfirst($key) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="rounded-md bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">{{ __('Pay now') }}</button>
                        </form>
                    @endif
                @endcan
                @can('create', \App\Models\Payment::class)
                    <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="rounded-md bg-primary px-3 py-1.5 text-white hover:bg-primary/95">{{ __('Record payment') }}</a>
                @endcan
            @endif
            @if($invoice->status === \App\Models\FeeInvoice::STATUS_DRAFT)
                <form method="POST" action="{{ route('fee-invoices.issue', $invoice) }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-md bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">{{ __('Issue invoice') }}</button>
                </form>
            @endif
            @can('cancel', $invoice)
                @if($invoice->status !== \App\Models\FeeInvoice::STATUS_CANCELLED)
                    <form method="POST" action="{{ route('fee-invoices.cancel', $invoice) }}" class="inline" onsubmit="this.reason.value = prompt({{ json_encode(__('Reason for cancellation?')) }}, ''); return !!this.reason.value;">
                        @csrf
                        <input type="hidden" name="reason" value="">
                        <button type="submit" class="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-red-700 hover:bg-red-100">{{ __('Cancel invoice') }}</button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-page">
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700">{{ __('Line items') }}</h2>
                    <span class="inline-flex rounded-full bg-page-soft px-2 py-0.5 text-xs font-medium capitalize">{{ str_replace('_', ' ', $invoice->status) }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-page-soft text-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left">{{ __('Description') }}</th>
                                <th class="px-4 py-2 text-left">{{ __('Category') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Qty') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Unit') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Total') }}</th>
                                @can('update', $invoice)<th class="px-4 py-2"></th>@endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($invoice->items as $item)
                                <tr>
                                    <td class="px-4 py-2">{{ $item->description }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $item->category }}</td>
                                    <td class="px-4 py-2 text-right">{{ $item->quantity }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ cedis($item->unit_amount) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ cedis($item->total_amount) }}</td>
                                    @can('update', $invoice)
                                        <td class="px-4 py-2 text-right">
                                            <form method="POST" action="{{ route('fee-invoice-items.destroy', [$invoice, $item]) }}" class="inline" onsubmit="return confirm({{ json_encode(__('Remove this item?')) }})">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:underline">{{ __('Remove') }}</button>
                                            </form>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">{{ __('No items yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @can('update', $invoice)
                    <form method="POST" action="{{ route('fee-invoice-items.store', $invoice) }}" class="flex flex-wrap items-end gap-2 border-t border-gray-200 bg-page-soft/40 p-3 text-sm">
                        @csrf
                        <input type="text" name="description" placeholder="{{ __('Description') }}" required class="flex-1 rounded border border-gray-300 bg-page px-2 py-1.5">
                        <input type="text" name="category" placeholder="{{ __('Category') }}" list="invoice-categories" class="w-36 rounded border border-gray-300 bg-page px-2 py-1.5">
                        <datalist id="invoice-categories">
                            @foreach (\App\Models\FeeInvoiceItem::SUGGESTED_CATEGORIES as $c)<option value="{{ $c }}">@endforeach
                        </datalist>
                        <input type="number" name="quantity" min="1" value="1" class="w-20 rounded border border-gray-300 bg-page px-2 py-1.5 text-right">
                        <input type="number" step="0.01" min="0" name="unit_amount" placeholder="{{ __('Unit') }}" required class="w-28 rounded border border-gray-300 bg-page px-2 py-1.5 text-right">
                        <button type="submit" class="rounded-md bg-primary px-3 py-1.5 text-white">{{ __('Add item') }}</button>
                    </form>
                @endcan
            </div>

            <div class="rounded-lg border border-gray-200 bg-page p-4">
                <h2 class="mb-3 text-sm font-semibold text-gray-700">{{ __('Payments') }}</h2>
                <ul class="divide-y divide-gray-100 text-sm">
                    @forelse($invoice->payments as $p)
                        <li class="flex justify-between gap-3 py-2 {{ $p->status === \App\Models\Payment::STATUS_REVERSED ? 'text-gray-400 line-through' : '' }}">
                            <span><a href="{{ route('payments.show', $p) }}" class="font-mono text-primary hover:underline">{{ $p->receipt_id }}</a> · {{ $p->date?->format('M j, Y') }} · {{ ucfirst($p->payment_channel) }}</span>
                            <span class="tabular-nums">{{ cedis($p->amount) }} <span class="text-xs uppercase ml-1">{{ $p->status }}</span></span>
                        </li>
                    @empty
                        <li class="py-2 text-gray-500">{{ __('No payments yet.') }}</li>
                    @endforelse
                </ul>
            </div>

            @if($invoice->adjustments->isNotEmpty())
            <div class="rounded-lg border border-gray-200 bg-page p-4">
                <h2 class="mb-3 text-sm font-semibold text-gray-700">{{ __('Adjustments') }}</h2>
                <ul class="divide-y divide-gray-100 text-sm">
                    @foreach($invoice->adjustments as $adj)
                        <li class="flex justify-between gap-3 py-2">
                            <span class="capitalize">{{ $adj->type }} · {{ $adj->description }} <span class="ml-1 text-xs text-gray-500">[{{ $adj->status }}]</span></span>
                            <span class="tabular-nums text-emerald-700">- {{ cedis($adj->amount) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <aside class="space-y-4">
            <div class="rounded-lg border border-gray-200 bg-page p-4 text-sm">
                <dl class="space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-500">{{ __('Subtotal') }}</dt><dd class="tabular-nums">{{ cedis($invoice->subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">{{ __('Discounts') }}</dt><dd class="tabular-nums text-emerald-700">-{{ cedis($invoice->discount_total) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">{{ __('Waivers') }}</dt><dd class="tabular-nums text-emerald-700">-{{ cedis($invoice->waiver_total) }}</dd></div>
                    <div class="flex justify-between border-t border-gray-200 pt-2"><dt class="font-medium text-gray-700">{{ __('Amount due') }}</dt><dd class="tabular-nums font-semibold">{{ cedis($invoice->amount_due) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">{{ __('Paid') }}</dt><dd class="tabular-nums text-emerald-700">{{ cedis($invoice->amount_paid) }}</dd></div>
                    <div class="flex justify-between border-t border-gray-200 pt-2"><dt class="font-medium text-gray-900">{{ __('Balance') }}</dt><dd class="tabular-nums text-lg font-semibold">{{ cedis($invoice->balance) }}</dd></div>
                    @if($arrears > 0)
                        <div class="mt-3 rounded-md border border-amber-300 bg-amber-50 p-2 text-xs text-amber-800">
                            <strong>{{ __('Other arrears: :a', ['a' => cedis($arrears)]) }}</strong> {{ __('(from other unpaid invoices)') }}
                        </div>
                    @endif
                </dl>
            </div>

            <div class="rounded-lg border border-gray-200 bg-page p-4 text-sm">
                <dl class="space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-500">{{ __('Issued') }}</dt><dd>{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">{{ __('Due') }}</dt><dd>{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">{{ __('Created by') }}</dt><dd>{{ $invoice->creator?->name ?? '—' }}</dd></div>
                </dl>
            </div>
        </aside>
    </div>
@endsection
