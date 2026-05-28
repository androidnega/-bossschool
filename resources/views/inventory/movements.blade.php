@extends('layouts.app')

@section('title', __('Inventory movements'))
@section('header-title', __('Inventory — Movements'))

@section('content')
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('When') }}</th>
                    <th class="px-4 py-3">{{ __('Item') }}</th>
                    <th class="px-4 py-3">{{ __('Type') }}</th>
                    <th class="px-4 py-3">{{ __('Qty') }}</th>
                    <th class="px-4 py-3">{{ __('Reason') }}</th>
                    <th class="px-4 py-3">{{ __('By') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($movements as $m)
                    <tr>
                        <td class="px-4 py-3">{{ $m->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">{{ $m->item?->name }}</td>
                        <td class="px-4 py-3">{{ $m->movement_type }}</td>
                        <td class="px-4 py-3">{{ $m->quantity }}</td>
                        <td class="px-4 py-3">{{ $m->reason }}</td>
                        <td class="px-4 py-3">{{ $m->performer?->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">{{ __('No movements yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $movements->links() }}</div>
@endsection
