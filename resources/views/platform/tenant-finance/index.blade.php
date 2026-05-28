@extends('layouts.app')

@section('title', __('Finance'))

@section('header-title', __('Tenant finance'))

@section('content')
    @include('platform.tenants._control-nav', ['tenant' => $tenant])

    <h1 class="text-xl font-semibold text-slate-900">{{ __('Finance overview') }} — {{ $tenant->name }}</h1>
    <p class="mt-1 text-sm text-slate-600">{{ __('Read-only platform visibility. Data is scoped to this tenant only.') }}</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-emerald-50 p-4">
            <p class="text-xs font-medium text-emerald-900">{{ __('Payments total') }}</p>
            <p class="mt-1 text-xl font-semibold text-emerald-950">{{ cedis($paymentsTotal) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">{{ __('Today') }}</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">{{ cedis($paymentsToday) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">{{ __('This month') }}</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">{{ cedis($paymentsMonth) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-rose-50 p-4">
            <p class="text-xs font-medium text-rose-900">{{ __('Outstanding (est.)') }}</p>
            <p class="mt-1 text-xl font-semibold text-rose-950">{{ cedis($outstanding) }}</p>
            <p class="mt-1 text-xs text-rose-800">{{ __('Debtors') }}: {{ $debtors['count'] }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('Payment methods') }}</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-700">
                @forelse ($methods as $m)
                    <li>{{ $m->method }}: {{ $m->c }} · {{ cedis((float) $m->total) }}</li>
                @empty
                    <li class="text-slate-500">{{ __('No payments') }}</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('Fees by type') }}</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-700">
                @forelse ($feeTypes as $f)
                    <li>{{ $f->fee_type }}: {{ $f->c }} · {{ cedis((float) $f->total) }}</li>
                @empty
                    <li class="text-slate-500">{{ __('No fee rows') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="mt-8 rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('Recent payments') }}</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead><tr class="border-b border-slate-200 text-slate-600">
                    <th class="py-2 pe-4">{{ __('Date') }}</th>
                    <th class="py-2 pe-4">{{ __('Student') }}</th>
                    <th class="py-2 pe-4">{{ __('Amount') }}</th>
                    <th class="py-2">{{ __('Method') }}</th>
                </tr></thead>
                <tbody>
                    @foreach ($recentPayments as $p)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pe-4">{{ $p->date?->toDateString() }}</td>
                            <td class="py-2 pe-4">{{ $p->student?->name ?? '—' }}</td>
                            <td class="py-2 pe-4">{{ cedis((float) $p->amount) }}</td>
                            <td class="py-2">{{ $p->method }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
