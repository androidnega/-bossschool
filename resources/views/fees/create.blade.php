@extends('layouts.app')

@section('title', __('Add fee'))

@section('header-title', 'Fees')

@section('content')
    @include('finances._subnav')

    <div class="mb-6">
        <a href="{{ route('fees.index') }}" class="text-sm font-medium text-secondary hover:text-primary">← {{ __('Back to fees') }}</a>
        <h1 class="mt-2 text-2xl font-semibold text-primary">{{ __('Add fee') }}</h1>
    </div>

    <div class="max-w-xl rounded-lg border border-gray-200 bg-page p-6">
        <form method="POST" action="{{ route('fees.store') }}" class="space-y-6">
            @csrf
            @include('fees._fields', ['classes' => $classes, 'terms' => $terms, 'fee' => null])

            <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-6">
                <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Save fee') }}</button>
                <a href="{{ route('fees.index') }}" class="rounded-md border border-gray-300 bg-page px-4 py-2 text-sm font-medium text-gray-700 hover:bg-page-soft">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
