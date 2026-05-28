@extends('layouts.app')

@section('title', __('Maintenance'))

@section('header-title', __('Platform maintenance'))

@section('content')
    <div class="max-w-2xl space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h1 class="text-xl font-semibold text-slate-900">{{ __('Global maintenance') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('Tenant users are shown a maintenance page. SuperAdmin bypasses this mode.') }}</p>

            @if (session('status'))
                <div class="mt-4 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">{{ session('status') }}</div>
            @endif

            <dl class="mt-6 space-y-2 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Status') }}</dt><dd class="font-medium text-slate-900">{{ ($global?->is_enabled ?? false) ? __('Enabled') : __('Disabled') }}</dd></div>
                @if ($global?->message)
                    <div><dt class="text-slate-500">{{ __('Message') }}</dt><dd class="mt-1 text-slate-800">{{ $global->message }}</dd></div>
                @endif
            </dl>

            <div class="mt-6 flex flex-wrap gap-2">
                <form method="POST" action="{{ route('platform.maintenance.enable') }}" class="inline">
                    @csrf
                    <input type="hidden" name="message" value="{{ old('message', $global?->message ?? '') }}">
                    <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">{{ __('Enable now') }}</button>
                </form>
                <form method="POST" action="{{ route('platform.maintenance.disable') }}" class="inline" onsubmit="return confirm(@json(__('Disable global maintenance?')));">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">{{ __('Disable') }}</button>
                </form>
            </div>

            <form method="POST" action="{{ route('platform.maintenance.update') }}" class="mt-8 space-y-4 border-t border-slate-200 pt-6">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="message">{{ __('Message') }}</label>
                    <textarea id="message" name="message" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('message', $global?->message) }}</textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="starts_at">{{ __('Starts at') }}</label>
                        <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', optional($global?->starts_at)?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="ends_at">{{ __('Ends at') }}</label>
                        <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', optional($global?->ends_at)?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">{{ __('Save schedule & message') }}</button>
            </form>
        </div>
    </div>
@endsection
