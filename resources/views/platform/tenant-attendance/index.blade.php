@extends('layouts.app')

@section('title', __('Attendance'))

@section('header-title', __('Tenant attendance'))

@section('content')
    @include('platform.tenants._control-nav', ['tenant' => $tenant])

    <h1 class="text-xl font-semibold text-slate-900">{{ __('Attendance') }} — {{ $tenant->name }}</h1>
    <p class="mt-1 text-sm text-slate-600">{{ __('Today') }}: {{ $today }}</p>

    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-emerald-50 p-4 text-center">
            <p class="text-xs font-medium text-emerald-900">{{ __('Present') }}</p>
            <p class="mt-1 text-2xl font-semibold text-emerald-950">{{ $present }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-rose-50 p-4 text-center">
            <p class="text-xs font-medium text-rose-900">{{ __('Absent') }}</p>
            <p class="mt-1 text-2xl font-semibold text-rose-950">{{ $absent }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-amber-50 p-4 text-center">
            <p class="text-xs font-medium text-amber-900">{{ __('Late') }}</p>
            <p class="mt-1 text-2xl font-semibold text-amber-950">{{ $late }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-100 p-4 text-center">
            <p class="text-xs font-medium text-slate-700">{{ __('Excused') }}</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $excused }}</p>
        </div>
    </div>

    <div class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Date') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Student') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Status') }}</th>
                    <th class="px-4 py-3 font-medium text-slate-700">{{ __('Remarks') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($recent as $a)
                    <tr>
                        <td class="px-4 py-3">{{ $a->date?->toDateString() }}</td>
                        <td class="px-4 py-3">{{ $a->student?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $a->status }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $a->remarks ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
