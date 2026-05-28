@extends('layouts.app')

@section('title', __('Onboarding'))
@section('header-title', __('School onboarding'))

@section('content')
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4">
        <h2 class="mb-2 text-sm font-semibold">{{ __('Completion') }}</h2>
        <div class="h-3 rounded-full bg-slate-100">
            <div class="h-3 rounded-full bg-emerald-500" style="width: {{ $checklist['percent_complete'] }}%"></div>
        </div>
        <p class="mt-1 text-xs text-slate-500">{{ $checklist['percent_complete'] }}% {{ __('complete') }}</p>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Step') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($checklist['items'] as $item)
                    <tr>
                        <td class="px-4 py-3">{{ $item['label'] }} @if($item['detail']) <span class="text-xs text-slate-500">— {{ $item['detail'] }}</span>@endif</td>
                        <td class="px-4 py-3">
                            @if($item['done'])
                                <span class="inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">{{ __('Done') }}</span>
                            @else
                                <span class="inline-block rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">{{ __('Pending') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($item['route'])
                                <a href="{{ route($item['route']) }}" class="text-blue-700">{{ $item['done'] ? __('Review') : __('Set up') }}</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4">
        <h2 class="mb-2 text-sm font-semibold">{{ __('Plan usage') }}</h2>
        <ul class="text-sm">
            <li>{{ __('Plan') }}: <strong>{{ $planLimits['plan_name'] ?? __('No plan') }}</strong></li>
            <li>{{ __('Students') }}: {{ $planLimits['students_used'] }} / {{ $planLimits['max_students'] ?? '∞' }}</li>
            <li>{{ __('Staff') }}: {{ $planLimits['staff_used'] }} / {{ $planLimits['max_staff'] ?? '∞' }}</li>
        </ul>
    </div>
@endsection
