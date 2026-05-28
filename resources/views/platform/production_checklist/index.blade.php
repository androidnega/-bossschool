@extends('layouts.app')

@section('title', __('Production checklist'))
@section('header-title', __('Production checklist'))

@section('content')
    <p class="mb-3 text-sm">
        <strong>{{ __('Overall') }}:</strong>
        @php($overall = $summary['overall'])
        <span class="inline-block rounded-full px-2 py-0.5 text-xs
            @if($overall === 'ok') bg-emerald-100 text-emerald-700
            @elseif($overall === 'warn') bg-amber-100 text-amber-700
            @else bg-rose-100 text-rose-700 @endif">
            {{ strtoupper($overall) }}
        </span>
        <span class="ml-3 text-xs text-slate-500">
            ok: {{ $summary['stats']['ok'] ?? 0 }} ·
            warn: {{ $summary['stats']['warn'] ?? 0 }} ·
            fail: {{ $summary['stats']['fail'] ?? 0 }}
        </span>
    </p>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Check') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Message') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($summary['checks'] as $c)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $c['label'] }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block rounded-full px-2 py-0.5 text-xs
                                @if($c['status'] === 'ok') bg-emerald-100 text-emerald-700
                                @elseif($c['status'] === 'warn') bg-amber-100 text-amber-700
                                @else bg-rose-100 text-rose-700 @endif">{{ strtoupper($c['status']) }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $c['message'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
