@extends('layouts.app')

@section('title', __('Online payment transactions'))
@section('header-title', __('Online payment transactions'))

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">{{ __('Online payment transactions') }}</h1>
        <form method="GET" class="flex items-center gap-2 text-sm">
            <select name="status" class="rounded-md border border-gray-300 px-2 py-1.5">
                <option value="">{{ __('All statuses') }}</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-slate-700 px-3 py-1.5 text-white">{{ __('Filter') }}</button>
        </form>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('When') }}</th>
                    <th class="px-4 py-3">{{ __('Student') }}</th>
                    <th class="px-4 py-3">{{ __('Invoice') }}</th>
                    <th class="px-4 py-3">{{ __('Provider') }}</th>
                    <th class="px-4 py-3">{{ __('Reference') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Amount') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $tx)
                    <tr>
                        <td class="px-4 py-3 text-slate-700">{{ $tx->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">{{ $tx->student?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($tx->invoice)
                                <a class="text-primary hover:underline" href="{{ route('fee-invoices.show', $tx->invoice) }}">{{ $tx->invoice->invoice_number }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ ucfirst($tx->provider) }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $tx->provider_reference }}</td>
                        <td class="px-4 py-3 text-right">GH₵ {{ number_format((float) $tx->amount, 2) }}</td>
                        <td class="px-4 py-3">{{ $tx->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">{{ __('No transactions yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $transactions->links() }}</div>
@endsection
