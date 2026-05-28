@extends('layouts.app')

@section('title', __('Receipt').' '.$payment->receipt_id)

@section('header-title', 'Fees')

@section('content')
    @include('finances._subnav')

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('payments.index') }}" class="text-sm font-medium text-secondary hover:text-primary">← {{ __('Back to payments') }}</a>
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('payments.receipt.pdf', $payment) }}" class="rounded-md border border-gray-300 bg-page px-3 py-1.5 hover:bg-page-soft">{{ __('Download PDF') }}</a>
            <button type="button" onclick="window.print()" class="hidden rounded-md border border-gray-300 bg-page px-3 py-1.5 hover:bg-page-soft sm:inline-block">{{ __('Print') }}</button>
            @can('reverse', $payment)
                <form method="POST" action="{{ route('payments.reverse', $payment) }}" onsubmit="this.reason.value = prompt({{ json_encode(__('Reason for reversal?')) }}, ''); return !!this.reason.value;">
                    @csrf
                    <input type="hidden" name="reason" value="">
                    <input type="hidden" name="confirm" value="1">
                    <button type="submit" class="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-red-700 hover:bg-red-100">{{ __('Reverse payment') }}</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="mx-auto max-w-2xl rounded-lg border border-gray-200 bg-page p-8 print:border-0 print:shadow-none">
        <div class="border-b border-gray-200 pb-6 text-center">
            <p class="text-lg font-semibold text-primary">{{ app('currentTenant')->name }}</p>
            <p class="mt-1 text-sm text-gray-600">{{ __('Payment receipt') }}</p>
            <p class="mt-3 font-mono text-sm text-gray-900">{{ $payment->receipt_id }}</p>
            @if($payment->status === \App\Models\Payment::STATUS_REVERSED)
                <p class="mt-2 inline-flex rounded-full bg-red-100 px-3 py-0.5 text-xs font-semibold text-red-700">{{ __('REVERSED') }}</p>
            @endif
        </div>

        <dl class="mt-6 space-y-4 text-sm">
            <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                <dt class="text-gray-500">{{ __('Date') }}</dt>
                <dd class="font-medium text-gray-900">{{ \Illuminate\Support\Carbon::parse($payment->date)->format('F j, Y') }}</dd>
            </div>
            <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                <dt class="text-gray-500">{{ __('Student') }}</dt>
                <dd class="text-right font-medium text-gray-900">{{ $payment->student?->name }}</dd>
            </div>
            @if($payment->invoice)
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">{{ __('Invoice') }}</dt>
                    <dd class="text-right"><a href="{{ route('fee-invoices.show', $payment->invoice) }}" class="font-mono text-primary hover:underline">{{ $payment->invoice->invoice_number }}</a></dd>
                </div>
            @endif
            @if($payment->student?->schoolClass)
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">{{ __('Class') }}</dt>
                    <dd class="text-right text-gray-900">{{ $payment->student->schoolClass->name }}@if($payment->student->schoolClass->section) — {{ $payment->student->schoolClass->section }}@endif</dd>
                </div>
            @endif
            <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                <dt class="text-gray-500">{{ __('Amount') }}</dt>
                <dd class="text-right text-lg font-semibold tabular-nums text-stone-900">{{ cedis($payment->amount) }}</dd>
            </div>
            <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                <dt class="text-gray-500">{{ __('Channel') }}</dt>
                <dd class="text-right text-gray-900">{{ ucfirst($payment->payment_channel) }}</dd>
            </div>
            @if($payment->payment_reference || $payment->reference)
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                    <dt class="text-gray-500">{{ __('Reference') }}</dt>
                    <dd class="text-right text-gray-900">{{ $payment->payment_reference ?: $payment->reference }}</dd>
                </div>
            @endif
            <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                <dt class="text-gray-500">{{ __('Received by') }}</dt>
                <dd class="text-right text-gray-900">{{ $payment->receiver?->name ?? '—' }}</dd>
            </div>
            @if($payment->status === \App\Models\Payment::STATUS_REVERSED)
                <div class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    <strong>{{ __('Reversed.') }}</strong> {{ $payment->reversal_reason }}
                </div>
            @endif
        </dl>
    </div>
@endsection
