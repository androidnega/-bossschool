@extends('layouts.app')

@section('title', __('Add staff'))

@section('header-title', __('Staff'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('staff.index') }}" class="text-sm font-medium text-secondary hover:text-primary">← {{ __('Back to staff') }}</a>
        <h1 class="mt-2 text-2xl font-semibold text-slate-900">{{ __('Add staff member') }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ __('Directory entry only — this does not create a login account.') }}</p>
    </div>

    <div class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        <form method="POST" action="{{ route('staff.store') }}" class="space-y-6">
            @csrf
            @include('staff._fields', ['staff' => null])

            <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-6">
                <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/95">{{ __('Save') }}</button>
                <a href="{{ route('staff.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
