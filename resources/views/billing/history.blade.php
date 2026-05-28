@extends('layouts.app')

@section('title', __('Billing history'))

@section('header-title', __('Billing'))

@section('content')
    @include('billing._subnav')

    <h1 class="text-2xl font-semibold text-primary">{{ __('Subscription history') }}</h1>
    <p class="mt-1 text-sm text-gray-600">{{ __('Past and current subscription records.') }}</p>

    <div class="mt-8 overflow-hidden rounded-lg border border-gray-200 bg-page">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-page-soft">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Plan') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Start') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('End') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-page">
                    @forelse ($subscriptions as $sub)
                        <tr class="hover:bg-page-soft/80">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $sub->plan?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $sub->start_date?->format('M j, Y') }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $sub->end_date?->format('M j, Y') ?? '—' }}</td>
                            <td class="px-4 py-3 capitalize text-gray-700">{{ str_replace('_', ' ', $sub->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-600">{{ __('No subscription history yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($subscriptions->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $subscriptions->links() }}</div>
        @endif
    </div>
@endsection
