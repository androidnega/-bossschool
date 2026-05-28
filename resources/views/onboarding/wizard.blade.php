@extends('layouts.app')

@section('title', __('Pilot onboarding wizard'))
@section('header-title', __('Pilot onboarding wizard'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    @php $done = collect($steps)->where('done', true)->count(); $total = count($steps); $pct = $total ? (int) round($done/$total*100) : 0; @endphp

    <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4 text-sm">
        <div class="flex items-center justify-between">
            <span>{{ __('Progress') }}: <strong>{{ $done }}/{{ $total }}</strong> ({{ $pct }}%)</span>
            @if($finished)
                <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">{{ __('Onboarding complete') }}</span>
            @endif
        </div>
        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100">
            <div class="h-2 bg-primary" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    <ol class="space-y-2">
        @foreach($steps as $s)
            <li class="flex items-center justify-between rounded-lg border {{ $s['done'] ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-white' }} p-3 text-sm">
                <div>
                    <strong>{{ __('Step') }} {{ $s['step'] }}.</strong> {{ $s['label'] }}
                    @if($s['essential']) <span class="text-xs text-rose-600">({{ __('required') }})</span> @endif
                </div>
                <div class="flex items-center gap-2">
                    @if($s['done'])
                        <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">{{ __('Done') }}</span>
                    @endif
                    <a href="{{ route('onboarding.wizard.show', ['step' => $s['step']]) }}" class="rounded-md border border-slate-300 px-2 py-1 text-xs">{{ __('Open') }}</a>
                </div>
            </li>
        @endforeach
    </ol>

    <div class="mt-6 flex items-center justify-end gap-2">
        <form method="POST" action="{{ route('onboarding.wizard.finish') }}">
            @csrf
            <button type="submit" @if(!$can_finish || $finished) disabled @endif
                class="rounded-md bg-primary px-3 py-1.5 text-white disabled:opacity-50">
                {{ $finished ? __('Onboarding complete') : __('Finish onboarding') }}
            </button>
        </form>
    </div>
@endsection
