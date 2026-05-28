@extends('layouts.app')

@section('title', __('Record payment'))

@section('header-title', 'Fees')

@section('content')
    @include('finances._subnav')

    <div class="mb-6">
        <a href="{{ route('payments.index') }}" class="text-sm font-medium text-secondary hover:text-primary">← {{ __('Back to payments') }}</a>
        <h1 class="mt-2 text-2xl font-semibold text-primary">{{ __('Record payment') }}</h1>
    </div>

    <div class="max-w-xl rounded-lg border border-gray-200 bg-page p-6">
        <form method="POST" action="{{ route('payments.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700">{{ __('Student') }} <span class="text-red-600">*</span></label>
                <select id="student_id" name="student_id" required
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('student_id') border-red-500 @enderror">
                    <option value="">—</option>
                    @foreach ($students as $stu)
                        <option value="{{ $stu->id }}" @selected((string) old('student_id', $invoice?->student_id) === (string) $stu->id)>
                            {{ $stu->name }}
                            @if($stu->schoolClass)
                                ({{ $stu->schoolClass->name }}@if($stu->schoolClass->section) — {{ $stu->schoolClass->section }}@endif)
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('student_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if($invoice)
                <div class="rounded-md border border-gray-200 bg-page-soft p-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('Linked invoice:') }}</span>
                        <span class="font-mono">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-xs text-gray-500">
                        <span>{{ __('Outstanding balance') }}</span>
                        <span class="tabular-nums">{{ cedis($invoice->balance) }}</span>
                    </div>
                    <input type="hidden" name="fee_invoice_id" value="{{ $invoice->id }}">
                </div>
            @endif

            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">{{ __('Amount (GH₵)') }} <span class="text-red-600">*</span></label>
                <input id="amount" name="amount" type="number" step="0.01" min="0.01" required value="{{ old('amount') }}"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('amount') border-red-500 @enderror">
                @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="payment_channel" class="block text-sm font-medium text-gray-700">{{ __('Channel') }} <span class="text-red-600">*</span></label>
                <select id="payment_channel" name="payment_channel" required
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('payment_channel') border-red-500 @enderror">
                    @foreach (\App\Models\Payment::CHANNELS as $ch)
                        <option value="{{ $ch }}" @selected(old('payment_channel', old('method', 'cash')) === $ch)>{{ ucfirst($ch) }}</option>
                    @endforeach
                </select>
                @error('payment_channel')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('method')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reference" class="block text-sm font-medium text-gray-700">{{ __('Reference') }}</label>
                <input id="reference" name="reference" type="text" value="{{ old('reference') }}"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('reference') border-red-500 @enderror">
                @error('reference')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="date" class="block text-sm font-medium text-gray-700">{{ __('Date') }} <span class="text-red-600">*</span></label>
                <input id="date" name="date" type="date" required value="{{ old('date', now()->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('date') border-red-500 @enderror">
                @error('date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-6">
                <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Save payment') }}</button>
                <a href="{{ route('payments.index') }}" class="rounded-md border border-gray-300 bg-page px-4 py-2 text-sm font-medium text-gray-700 hover:bg-page-soft">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
