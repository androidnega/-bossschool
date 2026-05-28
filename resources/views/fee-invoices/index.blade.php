@extends('layouts.app')

@section('title', __('Invoices'))

@section('header-title', 'Fees')

@section('content')
    @include('finances._subnav')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-primary">{{ __('Fee invoices') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Per-student bills with line items, discounts, waivers, and arrears.') }}</p>
        </div>
        @can('create', \App\Models\FeeInvoice::class)
            <a href="{{ route('fee-invoices.create') }}" class="inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('New invoice') }}</a>
        @endcan
    </div>

    @if(session('status'))
        <div class="mt-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="GET" class="mt-4 flex flex-wrap items-end gap-2 text-sm">
        @if(! $students->isEmpty())
            <select name="student_id" class="rounded-md border border-gray-300 bg-page px-2 py-1.5">
                <option value="">{{ __('All students') }}</option>
                @foreach ($students as $s)
                    <option value="{{ $s->id }}" @selected((string) request('student_id') === (string) $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        @endif
        <select name="status" class="rounded-md border border-gray-300 bg-page px-2 py-1.5">
            <option value="">{{ __('Any status') }}</option>
            @foreach (\App\Models\FeeInvoice::STATUSES as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ __(ucwords(str_replace('_', ' ', $st))) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md border border-gray-300 bg-page px-3 py-1.5 hover:bg-page-soft">{{ __('Filter') }}</button>
    </form>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-page">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-page-soft">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Invoice') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Student') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Period') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Due') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Paid') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Balance') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($invoices as $i)
                        <tr class="hover:bg-page-soft/80">
                            <td class="px-4 py-3"><a href="{{ route('fee-invoices.show', $i) }}" class="font-mono text-primary hover:underline">{{ $i->invoice_number }}</a></td>
                            <td class="px-4 py-3 text-gray-900">{{ $i->student?->name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $i->term?->name }} · {{ $i->academicYear?->name }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ cedis($i->amount_due) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-emerald-700">{{ cedis($i->amount_paid) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-stone-900 font-semibold">{{ cedis($i->balance) }}</td>
                            <td class="px-4 py-3"><span class="inline-flex rounded-full bg-page-soft px-2 py-0.5 text-xs font-medium capitalize text-gray-700">{{ str_replace('_', ' ', $i->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">{{ __('No invoices yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $invoices->links() }}</div>
        @endif
    </div>
@endsection
