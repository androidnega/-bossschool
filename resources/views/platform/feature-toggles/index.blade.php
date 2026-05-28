@extends('layouts.app')

@section('title', __('Feature toggles'))

@section('header-title', __('Feature toggles'))

@section('content')
    @php
        $totalCount = $toggles->count();
        $enabledCount = $toggles->filter(fn ($t) => (bool) ($t->is_enabled ?? false))->count();
    @endphp

    <div class="mx-auto max-w-6xl space-y-3">
        <header class="flex flex-wrap items-center justify-between gap-2 border-b border-stone-200 pb-2.5">
            <div class="min-w-0">
                <h1 class="text-sm font-semibold text-stone-900">{{ __('Global feature toggles') }}</h1>
                <p class="mt-0.5 text-xs text-stone-500">{{ __('When off, matching routes are unavailable for school users.') }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <span class="tabular-nums text-[0.65rem] font-medium text-stone-400">{{ $enabledCount }}/{{ $totalCount }}</span>
                @if ($toggles->isNotEmpty())
                    <button
                        form="feature-toggles-form"
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-md border border-stone-200 bg-white px-2.5 py-1 text-[0.7rem] font-semibold text-stone-700 transition hover:border-stone-300 hover:bg-stone-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:ring-offset-1">
                        <i class="fa-solid fa-check text-[0.6rem] text-primary" aria-hidden="true"></i>
                        {{ __('Save') }}
                    </button>
                @endif
            </div>
        </header>

        @if (session('status'))
            <div class="flex items-center gap-2 rounded-md border border-emerald-200/80 bg-emerald-50/90 px-2.5 py-1.5 text-xs text-emerald-900" role="status">
                <i class="fa-solid fa-circle-check text-emerald-600" aria-hidden="true"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form id="feature-toggles-form" method="POST" action="{{ route('platform.feature-toggles.update') }}">
            @csrf
            @method('PUT')

            @if ($toggles->isNotEmpty())
                <div class="grid grid-cols-2 gap-x-2 gap-y-1.5 md:grid-cols-4">
                    @foreach ($toggles as $toggle)
                        @php
                            $key = (string) $toggle->key;
                            $isEnabled = (bool) old('toggles.'.$key, $toggle->is_enabled ?? false);
                            $inputId = 'toggle-'.$key;
                            $hint = trim((string) ($toggle->description ?? ''));
                            $title = $hint !== '' ? $toggle->name.' — '.$hint : $toggle->name;
                        @endphp
                        <label
                            for="{{ $inputId }}"
                            class="flex min-h-[2.25rem] cursor-pointer items-center justify-between gap-2 rounded-md border border-stone-200/90 bg-white px-2 py-1 transition-colors hover:border-stone-300 hover:bg-stone-50/80 has-[:checked]:border-emerald-300/70 has-[:checked]:bg-emerald-50/35"
                            title="{{ $title }}">
                            <input
                                type="checkbox"
                                id="{{ $inputId }}"
                                name="toggles[{{ $key }}]"
                                value="1"
                                class="peer sr-only"
                                @checked($isEnabled)>

                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[0.7rem] font-medium leading-tight text-stone-800">{{ $toggle->name }}</span>
                                <code class="mt-0.5 block truncate font-mono text-[0.6rem] leading-none text-stone-400">{{ $key }}</code>
                            </span>

                            <span
                                class="inline-flex h-[18px] w-[31px] shrink-0 items-center justify-start rounded-full bg-stone-300/95 p-[2px] transition-[background-color,justify-content] duration-150 ease-out peer-checked:justify-end peer-checked:bg-emerald-500 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-1 peer-focus-visible:outline-emerald-500/40"
                                aria-hidden="true">
                                <span class="block size-3.5 rounded-full bg-white shadow-sm ring-1 ring-black/[0.06]"></span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="rounded-md border border-dashed border-stone-200 py-8 text-center text-xs text-stone-500">
                    {{ __('No feature toggles defined.') }}
                </div>
            @endif
        </form>
    </div>
@endsection
