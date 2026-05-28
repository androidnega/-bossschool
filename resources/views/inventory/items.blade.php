@extends('layouts.app')

@section('title', __('Inventory items'))
@section('header-title', __('Inventory — Items'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <div class="mb-3 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('inventory.low-stock') }}" class="rounded-md border border-slate-300 px-3 py-1.5">{{ __('Low stock report') }}</a>
        <a href="{{ route('inventory.movements') }}" class="rounded-md border border-slate-300 px-3 py-1.5">{{ __('Movements log') }}</a>
    </div>

    @can('create', \App\Models\InventoryItem::class)
        <form method="POST" action="{{ route('inventory.items.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-3">
            @csrf
            <input type="text" name="name" required placeholder="{{ __('Item name') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="text" name="category" placeholder="{{ __('Category') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="text" name="sku" placeholder="{{ __('SKU') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="number" min="0" name="quantity_on_hand" required value="0" placeholder="{{ __('Quantity on hand') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="number" min="0" name="reorder_level" value="0" placeholder="{{ __('Reorder level') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="number" min="0" step="0.01" name="unit_cost" placeholder="{{ __('Unit cost (GHS)') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="text" name="location" placeholder="{{ __('Location') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm sm:col-span-3" />
            <button type="submit" class="sm:col-span-3 rounded-md bg-primary px-3 py-2 text-sm text-white">{{ __('Add item') }}</button>
        </form>
    @endcan

    @can('create', \App\Models\InventoryItem::class)
        <form method="POST" action="{{ route('inventory.movements.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-3">
            @csrf
            <select name="inventory_item_id" required class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                <option value="">{{ __('Item') }}</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->quantity_on_hand }})</option>
                @endforeach
            </select>
            <select name="movement_type" required class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                @foreach(\App\Models\InventoryMovement::TYPES as $t)
                    <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            <input type="number" name="quantity" min="1" required placeholder="{{ __('Quantity') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            <input type="text" name="reason" placeholder="{{ __('Reason / note') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm sm:col-span-3" />
            <button type="submit" class="sm:col-span-3 rounded-md bg-emerald-600 px-3 py-2 text-sm text-white">{{ __('Record movement') }}</button>
        </form>
    @endcan

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Item') }}</th>
                    <th class="px-4 py-3">{{ __('Category') }}</th>
                    <th class="px-4 py-3">{{ __('SKU') }}</th>
                    <th class="px-4 py-3">{{ __('On hand') }}</th>
                    <th class="px-4 py-3">{{ __('Reorder') }}</th>
                    <th class="px-4 py-3">{{ __('Unit cost') }}</th>
                    <th class="px-4 py-3">{{ __('Location') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $item)
                    <tr class="@if($item->isLowStock()) bg-amber-50 @endif">
                        <td class="px-4 py-3">{{ $item->name }}</td>
                        <td class="px-4 py-3">{{ $item->category }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $item->sku }}</td>
                        <td class="px-4 py-3">{{ $item->quantity_on_hand }}</td>
                        <td class="px-4 py-3">{{ $item->reorder_level }}</td>
                        <td class="px-4 py-3">{{ $item->unit_cost !== null ? 'GHS '.number_format((float) $item->unit_cost, 2) : '—' }}</td>
                        <td class="px-4 py-3">{{ $item->location }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">{{ __('No items yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $items->links() }}</div>
@endsection
