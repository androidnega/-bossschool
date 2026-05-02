@extends('layouts.app')

@section('title', 'Dashboard — '.config('app.name'))

@section('content')
    <h1 class="text-2xl font-semibold text-primary">Dashboard</h1>
    <p class="mt-1 text-sm text-gray-600">Overview for your school.</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-secondary">Total students</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($studentCount) }}</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-secondary">Fees collected</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($feesCollected, 2) }}</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm sm:col-span-2 lg:col-span-1">
            <p class="text-sm font-medium text-secondary">Outstanding fees</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($outstandingFees, 2) }}</p>
            <p class="mt-2 text-xs text-gray-500">Based on fee definitions minus payments recorded.</p>
        </div>
    </div>
@endsection
