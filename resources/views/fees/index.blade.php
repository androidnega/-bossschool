@extends('layouts.app')

@section('title', __('Fees'))

@section('header-title', 'Fees')

@section('content')
    @include('finances._subnav')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-primary">{{ __('Fee structure') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Fees by class and term.') }}</p>
        </div>
        @can('create', \App\Models\Fee::class)
            <a href="{{ route('fees.create') }}" class="inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Add fee') }}</a>
        @endcan
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-page">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-page-soft">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Class') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Term') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Type') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Amount') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-page">
                    @forelse ($fees as $fee)
                        <tr class="hover:bg-page-soft/80">
                            <td class="px-4 py-3 text-gray-900">{{ $fee->schoolClass?->name }}@if($fee->schoolClass?->section) — {{ $fee->schoolClass->section }}@endif</td>
                            <td class="px-4 py-3 text-gray-700">{{ $fee->term?->name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $fee->fee_type }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ cedis($fee->amount) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @can('update', $fee)
                                    <a href="{{ route('fees.edit', $fee) }}" class="text-primary hover:underline">{{ __('Edit') }}</a>
                                @endcan
                                @can('delete', $fee)
                                    <span class="text-gray-300">·</span>
                                    <form action="{{ route('fees.destroy', $fee) }}" method="POST" class="inline" onsubmit="return confirm({{ json_encode(__('Remove this fee?')) }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-600">{{ __('No fees defined yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($fees->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $fees->links() }}</div>
        @endif
    </div>
@endsection
