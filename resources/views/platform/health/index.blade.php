@extends('layouts.app')

@section('title', __('System health'))
@section('header-title', __('System health'))

@section('content')
    <p class="mb-4 text-sm"><strong>{{ __('Overall status') }}:</strong>
        @if($result['status'] === 'ok')
            <span class="inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">OK</span>
        @else
            <span class="inline-block rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">{{ strtoupper($result['status']) }}</span>
        @endif
    </p>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Check') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Detail') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($result['checks'] as $name => $check)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $name }}</td>
                        <td class="px-4 py-3">
                            @if($check['ok'] ?? false)
                                <span class="inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">OK</span>
                            @else
                                <span class="inline-block rounded-full bg-rose-100 px-2 py-0.5 text-xs text-rose-700">FAIL</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <div>{{ $check['message'] ?? '' }}</div>
                            <ul class="mt-1 text-slate-500">
                                @foreach($check as $k => $v)
                                    @if(! in_array($k, ['ok', 'message']))
                                        <li>{{ $k }}: {{ is_array($v) ? json_encode($v) : (string) $v }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="mt-3 text-xs text-slate-500">{{ __('No secrets, API keys, or DSNs are displayed here.') }}</p>
@endsection
