@extends('layouts.app')

@section('title', __('Edit plan'))

@section('header-title', $plan->name)

@section('content')
    <div class="max-w-xl rounded-xl border border-slate-200 bg-white p-6">
        <h1 class="text-xl font-semibold text-slate-900">{{ __('Edit plan') }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ __('Update pricing and limits. Changes are logged.') }}</p>

        <form method="POST" action="{{ route('platform.plans.update', $plan) }}" class="mt-8 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="name">{{ __('Name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name', $plan->name) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="price">{{ __('Price') }}</label>
                <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $plan->price) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('price')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="billing_cycle">{{ __('Billing cycle') }}</label>
                <select id="billing_cycle" name="billing_cycle" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="monthly" @selected(old('billing_cycle', $plan->billing_cycle) === 'monthly')>{{ __('Monthly') }}</option>
                    <option value="yearly" @selected(old('billing_cycle', $plan->billing_cycle) === 'yearly')>{{ __('Yearly') }}</option>
                </select>
                @error('billing_cycle')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="max_students">{{ __('Max students') }}</label>
                    <input id="max_students" name="max_students" type="number" min="0" value="{{ old('max_students', $plan->max_students) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('max_students')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="max_staff">{{ __('Max staff') }}</label>
                    <input id="max_staff" name="max_staff" type="number" min="0" value="{{ old('max_staff', $plan->max_staff) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('max_staff')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="sort_order">{{ __('Sort order') }}</label>
                <input id="sort_order" name="sort_order" type="number" min="0" max="32767" value="{{ old('sort_order', $plan->sort_order) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('sort_order')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="features_json">{{ __('Features (JSON)') }}</label>
                <textarea id="features_json" name="features_json" rows="5" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm">{{ old('features_json', json_encode($plan->features ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
                @error('features_json')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-3">
                <input id="is_active" name="is_active" type="checkbox" value="1" class="size-4 rounded border-slate-300" @checked(old('is_active', $plan->is_active))>
                <label for="is_active" class="text-sm text-slate-800">{{ __('Plan is active') }}</label>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">{{ __('Save') }}</button>
                <a href="{{ route('platform.plans.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
