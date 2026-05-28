@extends('layouts.app')

@section('title', __('Platform notices'))

@section('header-title', __('Platform notices'))

@section('content')
    {{-- TODO: Two-way messaging / replies — platform notices are one-way only. --}}
    <div class="border-b border-slate-200 pb-6">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('Platform notices') }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ __('Visible to Super Admins and published to all tenant schools. No per-school academic or fee content.') }}</p>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-xl border border-secondary/30 bg-page-soft px-4 py-3 text-sm text-slate-800" role="status">{{ session('status') }}</div>
    @endif

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-6">
            <h2 class="text-lg font-semibold text-slate-900"><i class="fa-solid fa-pen-to-square me-2 text-teal-700" aria-hidden="true"></i>{{ __('New platform notice') }}</h2>
            <form method="POST" action="{{ route('platform.notices.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="p-title" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Title') }} <span class="text-slate-400">({{ __('optional') }})</span></label>
                    <input id="p-title" name="title" type="text" value="{{ old('title') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label for="p-body" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Content') }}</label>
                    <textarea id="p-body" name="content" rows="4" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('content') }}</textarea>
                </div>
                <p class="text-xs text-slate-500">{{ __('Audience is fixed to all schools on the platform.') }}</p>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>{{ __('Publish') }}
                </button>
            </form>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Published') }}</h2>
            <ul class="mt-3 space-y-3">
                @forelse ($notices as $n)
                    <li class="rounded-xl border border-slate-200 bg-white p-4 text-sm">
                        <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-800">{{ __('Notice') }}</span>
                        @if ($n->title)
                            <p class="mt-2 font-medium text-slate-900">{{ $n->title }}</p>
                        @endif
                        <p class="mt-1 text-xs text-slate-500">{{ $n->sent_at?->diffForHumans() }} · {{ $n->sender?->name }}</p>
                        <p class="mt-2 text-slate-800">{{ $n->content }}</p>
                    </li>
                @empty
                    <li class="text-sm text-slate-600">{{ __('No platform notices yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
