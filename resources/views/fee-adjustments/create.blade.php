@extends('layouts.app')

@section('title', __('Request adjustment'))

@section('header-title', 'Fees')

@section('content')
    @include('finances._subnav')

    <div class="mb-6">
        <a href="{{ route('fee-adjustments.index') }}" class="text-sm font-medium text-secondary hover:text-primary">← {{ __('Back to adjustments') }}</a>
        <h1 class="mt-2 text-2xl font-semibold text-primary">{{ __('Request a discount, scholarship or waiver') }}</h1>
    </div>

    <form method="POST" action="{{ route('fee-adjustments.store') }}" class="max-w-xl space-y-4 rounded-lg border border-gray-200 bg-page p-6">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Student') }} <span class="text-red-600">*</span></label>
            <select name="student_id" required class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                <option value="">—</option>
                @foreach ($students as $s)<option value="{{ $s->id }}" @selected((string) old('student_id') === (string) $s->id)>{{ $s->name }}</option>@endforeach
            </select>
            @error('student_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Type') }} <span class="text-red-600">*</span></label>
            <select name="type" required class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                @foreach (\App\Models\FeeAdjustment::TYPES as $t)<option value="{{ $t }}" @selected(old('type') === $t)>{{ ucfirst($t) }}</option>@endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Amount (GH₵)') }} <span class="text-red-600">*</span></label>
            <input type="number" step="0.01" min="0.01" name="amount" required value="{{ old('amount') }}" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
            @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Invoice (optional)') }}</label>
            <select name="fee_invoice_id" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                <option value="">{{ __('Not tied to a specific invoice') }}</option>
                @foreach ($invoices as $inv)<option value="{{ $inv->id }}" @selected((string) old('fee_invoice_id') === (string) $inv->id)>{{ $inv->invoice_number }} · {{ $inv->student?->name }} · {{ cedis($inv->balance) }}</option>@endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
            <input type="text" name="description" maxlength="191" value="{{ old('description') }}" class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
        </div>

        <div class="flex gap-3 border-t border-gray-200 pt-6">
            <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Submit for approval') }}</button>
            <a href="{{ route('fee-adjustments.index') }}" class="rounded-md border border-gray-300 bg-page px-4 py-2 text-sm font-medium text-gray-700">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection
