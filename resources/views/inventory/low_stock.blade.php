@extends('layouts.app')

@section('title', __('Low stock'))
@section('header-title', __('Inventory — Low stock'))

@section('content')
    <p class="mb-4 text-sm text-slate-600">{{ __('Current valuation of low-stock items') }}: <strong>GHS {{ number_format($valuation, 2) }}</strong></p>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Item') }}</th>
                    <th class="px-4 py-3">{{ __('On hand') }}</th>
                    <th class="px-4 py-3">{{ __('Reorder level') }}</th>
                    <th class="px-4 py-3">{{ __('Unit cost') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $item)
                    <tr class="bg-amber-50">
                        <td class="px-4 py-3">{{ $item->name }}</td>
                        <td class="px-4 py-3">{{ $item->quantity_on_hand }}</td>
                        <td class="px-4 py-3">{{ $item->reorder_level }}</td>
                        <td class="px-4 py-3">{{ $item->unit_cost !== null ? 'GHS '.number_format((float) $item->unit_cost, 2) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">{{ __('No low-stock items.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $items->links() }}</div>
@endsection
