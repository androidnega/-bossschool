@extends('layouts.app')

@section('title', __('Subscriptions'))

@section('header-title', __('Subscriptions'))

@section('content')
    <h1 class="text-2xl font-semibold text-slate-900">{{ __('Subscription status') }}</h1>
    <p class="mt-1 text-sm text-slate-600">{{ __('Across all tenants (no school financial detail).') }}</p>

    <div class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('School') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Plan') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('End') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($subscriptions as $sub)
                    <tr>
                        <td class="px-4 py-3">{{ $sub->tenant?->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $sub->plan?->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $sub->status }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $sub->end_date?->toDateString() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $subscriptions->links() }}</div>
@endsection
