@extends('layouts.app')

@section('title', __('Edit academic year'))

@section('header-title', __('Settings'))

@section('content')
    @include('settings._subnav')

    <div class="mb-6">
        <a href="{{ route('academic-years.index') }}" class="text-sm font-medium text-secondary hover:text-primary">← {{ __('Back to academic years') }}</a>
        <h1 class="mt-2 text-2xl font-semibold text-primary">{{ __('Edit academic year') }}</h1>
    </div>

    <div class="max-w-2xl rounded-lg border border-gray-200 bg-page p-6">
        <form method="POST" action="{{ route('academic-years.update', $year) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('academic-years._fields', ['year' => $year])

            <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-6">
                <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Save') }}</button>
                <a href="{{ route('academic-years.index') }}" class="rounded-md border border-gray-300 bg-page px-4 py-2 text-sm font-medium text-gray-700 hover:bg-page-soft">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
