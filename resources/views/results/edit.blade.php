@extends('layouts.app')

@section('title', __('Edit result'))

@section('header-title', 'Results')

@section('content')
    @include('results._subnav')

    <div class="mb-6">
        <a href="{{ route('results.index') }}" class="text-sm font-medium text-secondary hover:text-primary">← {{ __('Back to results') }}</a>
        <h1 class="mt-2 text-2xl font-semibold text-primary">{{ __('Edit result') }}</h1>
    </div>

    <div class="max-w-2xl rounded-lg border border-gray-200 bg-page p-6">
        <form method="POST" action="{{ route('results.update', $result) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('results._fields', [
                'students' => $students,
                'subjects' => $subjects,
                'result' => $result,
                'years' => $years,
                'terms' => $terms,
                'currentYear' => $result->academicYear,
                'currentTerm' => $result->term,
                'canOverrideTerm' => $canOverrideTerm,
            ])

            <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-6">
                <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Save changes') }}</button>
                <a href="{{ route('results.index') }}" class="rounded-md border border-gray-300 bg-page px-4 py-2 text-sm font-medium text-gray-700 hover:bg-page-soft">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
