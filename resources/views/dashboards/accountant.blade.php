@extends('layouts.app')

@section('title', __('Accountant dashboard'))

@section('header-title', __('Accountant dashboard'))

@section('content')
    <div class="flex flex-col gap-3 border-b border-amber-200/60 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-amber-950">{{ __('Finance desk') }}</h1>
            <p class="mt-1 text-sm text-amber-900/75">{{ __('Collections, exposure, and receipt traffic.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('payments.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-800"><i class="fa-solid fa-receipt" aria-hidden="true"></i>{{ __('Record payment') }}</a>
            <a href="{{ route('debtors.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-user-clock" aria-hidden="true"></i>{{ __('Debtors') }}</a>
            <a href="{{ route('payments.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-list" aria-hidden="true"></i>{{ __('Payment history') }}</a>
            <a href="{{ route('reports.finance') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-file-lines" aria-hidden="true"></i>{{ __('Finance report') }}</a>
            <a href="{{ route('fees.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i>{{ __('Fees') }}</a>
        </div>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-dash-card icon="fa-solid fa-calendar-day" :label="__('Payments today')" :value="number_format($paymentsTodayCount)" variant="revenue" :hint="cedis($paymentsTodaySum)" />
        <x-dash-card icon="fa-solid fa-coins" :label="__('Total collections')" :value="cedis($totalCollections)" variant="revenue" />
        <x-dash-card icon="fa-solid fa-scale-unbalanced" :label="__('Outstanding')" :value="cedis($outstandingFees)" variant="debtors" />
        <x-dash-card icon="fa-solid fa-user-clock" :label="__('Debtors')" :value="number_format($debtorsCount)" variant="debtors" />
    </div>

    <div class="mt-10 grid gap-8 lg:grid-cols-2">
        <div>
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-credit-card text-emerald-700" aria-hidden="true"></i>{{ __('Payment methods') }}</h2>
            <ul class="mt-3 space-y-2 rounded-xl border border-slate-200 bg-white p-4 text-sm">
                @foreach ($methods as $row)
                    <li class="flex justify-between gap-2"><span class="capitalize"><i class="fa-solid fa-wallet me-1 text-slate-400" aria-hidden="true"></i>{{ $row->method }}</span><span class="text-slate-600">{{ $row->c }} · {{ cedis((float) $row->total) }}</span></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-receipt text-teal-700" aria-hidden="true"></i>{{ __('Recent payments') }}</h2>
            <div class="mt-3 space-y-2">
                @foreach ($recentPayments as $p)
                    <a href="{{ route('payments.show', $p) }}" class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm hover:bg-slate-50">
                        <span class="font-medium text-slate-900"><i class="fa-solid fa-user-graduate me-1 text-slate-400" aria-hidden="true"></i>{{ $p->student?->name }}</span>
                        <span class="font-semibold text-emerald-800">{{ cedis((float) $p->amount) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-10">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-ranking-star text-amber-700" aria-hidden="true"></i>{{ __('Highest debtors') }}</h2>
        <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2">{{ __('Student') }}</th>
                        <th class="px-4 py-2">{{ __('Balance') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($highestDebtors as $row)
                        <tr>
                            <td class="px-4 py-2">{{ $row['student']->name }}</td>
                            <td class="px-4 py-2 font-semibold text-amber-800">{{ cedis($row['balance']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-4 py-6 text-center text-slate-500">{{ __('No debtors.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
