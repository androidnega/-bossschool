@extends('layouts.app')

@section('title', __('Dashboard'))

@section('header-title', 'Dashboard')

@section('content')
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">{{ __('Dashboard') }}</h1>
            <p class="mt-1 flex items-center gap-2 text-sm text-stone-500">
                <i class="fa-solid fa-school text-stone-400" aria-hidden="true"></i>
                {{ __('Overview for your school.') }}
            </p>
        </div>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-stone-200/80 bg-card-mist p-6 ring-1 ring-stone-200/40">
            <div class="flex items-center justify-between gap-2">
                <span class="flex size-11 items-center justify-center rounded-xl bg-white/80 text-stone-700 ring-1 ring-stone-200/60">
                    <i class="fa-solid fa-users text-lg" aria-hidden="true"></i>
                </span>
                <span class="text-xs font-medium text-stone-500">{{ __('Roster') }}</span>
            </div>
            <p class="mt-5 text-sm font-medium text-stone-600">{{ __('Total students') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums tracking-tight text-stone-900 sm:text-3xl">{{ number_format($studentCount) }}</p>
        </div>

        <div class="rounded-2xl border border-stone-200/80 bg-card-sage p-6 ring-1 ring-stone-200/40">
            <div class="flex items-center justify-between gap-2">
                <span class="flex size-11 items-center justify-center rounded-xl bg-white/80 text-stone-700 ring-1 ring-stone-200/60">
                    <i class="fa-solid fa-user-check text-lg" aria-hidden="true"></i>
                </span>
                <span class="text-xs font-medium text-stone-500">{{ __('Active') }}</span>
            </div>
            <p class="mt-5 text-sm font-medium text-stone-600">{{ __('Active students') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums tracking-tight text-stone-900 sm:text-3xl">{{ number_format($activeStudentCount) }}</p>
        </div>

        <div class="rounded-2xl border border-stone-200/80 bg-card-sand p-6 ring-1 ring-stone-200/40">
            <div class="flex items-center justify-between gap-2">
                <span class="flex size-11 items-center justify-center rounded-xl bg-white/80 text-stone-700 ring-1 ring-stone-200/60">
                    <i class="fa-solid fa-sack-dollar text-lg" aria-hidden="true"></i>
                </span>
                <span class="text-xs font-medium text-stone-500">{{ __('Income') }}</span>
            </div>
            <p class="mt-5 text-sm font-medium text-stone-600">{{ __('Fees collected') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums tracking-tight text-stone-900 sm:text-3xl">{{ cedis($feesCollected) }}</p>
        </div>

        <div class="rounded-2xl border border-stone-200/80 bg-card-shell p-6 ring-1 ring-stone-200/40 sm:col-span-2 xl:col-span-1">
            <div class="flex items-center justify-between gap-2">
                <span class="flex size-11 items-center justify-center rounded-xl bg-white/80 text-stone-700 ring-1 ring-stone-200/60">
                    <i class="fa-solid fa-file-invoice-dollar text-lg" aria-hidden="true"></i>
                </span>
                <span class="text-xs font-medium text-stone-500">{{ __('Due') }}</span>
            </div>
            <p class="mt-5 text-sm font-medium text-stone-600">{{ __('Outstanding fees') }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums tracking-tight text-stone-900 sm:text-3xl">{{ cedis($outstandingFees) }}</p>
            <p class="mt-3 flex items-start gap-2 text-xs leading-relaxed text-stone-500">
                <i class="fa-solid fa-circle-info mt-0.5 shrink-0 text-stone-400" aria-hidden="true"></i>
                {{ __('Sum of each student’s expected class fees minus their payments.') }}
            </p>
        </div>
    </div>

    <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('students.index') }}" class="flex min-h-[4.25rem] items-center gap-4 rounded-2xl border border-stone-200/90 bg-white p-4 ring-1 ring-stone-100 transition hover:border-stone-300">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-card-mist text-stone-700 ring-1 ring-stone-200/50">
                <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
            </span>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-stone-900">{{ __('Students') }}</p>
                <p class="text-sm text-stone-500">{{ __('Manage roster and profiles') }}</p>
            </div>
            <i class="fa-solid fa-chevron-right shrink-0 text-xs text-stone-300" aria-hidden="true"></i>
        </a>
        @can('create', \App\Models\Payment::class)
            <a href="{{ route('payments.create') }}" class="flex min-h-[4.25rem] items-center gap-4 rounded-2xl border border-stone-200/90 bg-white p-4 ring-1 ring-stone-100 transition hover:border-stone-300">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-card-sage text-stone-700 ring-1 ring-stone-200/50">
                    <i class="fa-solid fa-receipt" aria-hidden="true"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-stone-900">{{ __('Record payment') }}</p>
                    <p class="text-sm text-stone-500">{{ __('Cash, MoMo, or bank') }}</p>
                </div>
                <i class="fa-solid fa-chevron-right shrink-0 text-xs text-stone-300" aria-hidden="true"></i>
            </a>
        @endcan
        <a href="{{ route('fees.index') }}" class="flex min-h-[4.25rem] items-center gap-4 rounded-2xl border border-stone-200/90 bg-white p-4 ring-1 ring-stone-100 transition hover:border-stone-300 sm:col-span-2 lg:col-span-1">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-card-sand text-stone-700 ring-1 ring-stone-200/50">
                <i class="fa-solid fa-coins" aria-hidden="true"></i>
            </span>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-stone-900">{{ __('Fees & debtors') }}</p>
                <p class="text-sm text-stone-500">{{ __('Structure, receipts, balances') }}</p>
            </div>
            <i class="fa-solid fa-chevron-right shrink-0 text-xs text-stone-300" aria-hidden="true"></i>
        </a>
        <a href="{{ route('results.index') }}" class="flex min-h-[4.25rem] items-center gap-4 rounded-2xl border border-stone-200/90 bg-white p-4 ring-1 ring-stone-100 transition hover:border-stone-300 sm:col-span-2 lg:col-span-1">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-card-shell text-stone-700 ring-1 ring-stone-200/50">
                <i class="fa-solid fa-chart-simple" aria-hidden="true"></i>
            </span>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-stone-900">{{ __('Results') }}</p>
                <p class="text-sm text-stone-500">{{ __('Subjects and grades') }}</p>
            </div>
            <i class="fa-solid fa-chevron-right shrink-0 text-xs text-stone-300" aria-hidden="true"></i>
        </a>
        <a href="{{ route('reports.index') }}" class="flex min-h-[4.25rem] items-center gap-4 rounded-2xl border border-stone-200/90 bg-white p-4 ring-1 ring-stone-100 transition hover:border-stone-300 sm:col-span-2 lg:col-span-1">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-card-mist text-stone-700 ring-1 ring-stone-200/50">
                <i class="fa-solid fa-chart-pie" aria-hidden="true"></i>
            </span>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-stone-900">{{ __('Reports') }}</p>
                <p class="text-sm text-stone-500">{{ __('Finance, students, academic') }}</p>
            </div>
            <i class="fa-solid fa-chevron-right shrink-0 text-xs text-stone-300" aria-hidden="true"></i>
        </a>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-2">
        @can('viewAny', \App\Models\Payment::class)
            <div class="overflow-hidden rounded-2xl border border-stone-200/90 bg-white ring-1 ring-stone-100">
                <div class="flex items-center justify-between gap-3 border-b border-stone-100 bg-white px-4 py-3">
                    <h2 class="text-sm font-semibold text-stone-800">{{ __('Recent payments') }}</h2>
                    <a href="{{ route('payments.index') }}" class="text-xs font-medium text-stone-500 hover:text-stone-800">{{ __('View all') }}</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-stone-100 bg-stone-50/80 text-xs text-stone-500">
                            <tr>
                                <th class="px-4 py-2 font-medium">{{ __('Date') }}</th>
                                <th class="px-4 py-2 font-medium">{{ __('Student') }}</th>
                                <th class="hidden px-4 py-2 font-medium sm:table-cell">{{ __('Method') }}</th>
                                <th class="px-4 py-2 text-right font-medium">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($recentPayments as $payment)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-2.5 text-stone-600">{{ $payment->date?->format('M j, Y') }}</td>
                                    <td class="px-4 py-2.5">
                                        <a href="{{ route('payments.show', $payment) }}" class="font-medium text-stone-800 hover:underline">{{ $payment->student?->name }}</a>
                                        <div class="text-xs text-stone-500 sm:hidden">{{ match ($payment->method) { 'momo' => 'MoMo', 'cash' => __('Cash'), 'bank' => __('Bank'), default => $payment->method } }}</div>
                                    </td>
                                    <td class="hidden px-4 py-2.5 text-stone-600 sm:table-cell">{{ match ($payment->method) { 'momo' => 'MoMo', 'cash' => __('Cash'), 'bank' => __('Bank'), default => $payment->method } }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-stone-900">{{ cedis($payment->amount) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-stone-500">{{ __('No payments yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endcan

        <div class="overflow-hidden rounded-2xl border border-stone-200/90 bg-white ring-1 ring-stone-100">
            <div class="flex items-center justify-between gap-3 border-b border-stone-100 bg-white px-4 py-3">
                <h2 class="text-sm font-semibold text-stone-800">{{ __('Recent students') }}</h2>
                <a href="{{ route('students.index') }}" class="text-xs font-medium text-stone-500 hover:text-stone-800">{{ __('View all') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-stone-100 bg-stone-50/80 text-xs text-stone-500">
                        <tr>
                            <th class="px-4 py-2 font-medium">{{ __('Name') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('Class') }}</th>
                            <th class="hidden px-4 py-2 font-medium md:table-cell">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($recentStudents as $student)
                            <tr>
                                <td class="px-4 py-2.5">
                                    <a href="{{ route('students.show', $student) }}" class="font-medium text-stone-800 hover:underline">{{ $student->name }}</a>
                                    <div class="text-xs text-stone-500 md:hidden">{{ ucfirst($student->status) }}</div>
                                </td>
                                <td class="px-4 py-2.5 text-stone-600">{{ $student->schoolClass?->name }}@if($student->schoolClass?->section) — {{ $student->schoolClass->section }}@endif</td>
                                <td class="hidden px-4 py-2.5 text-stone-600 md:table-cell">{{ ucfirst($student->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-stone-500">{{ __('No students yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('viewAny', \App\Models\Fee::class)
        <div class="mt-8 overflow-hidden rounded-2xl border border-stone-200/90 bg-white ring-1 ring-stone-100">
            <div class="flex flex-col gap-2 border-b border-stone-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-sm font-semibold text-stone-800">{{ __('Top debtors') }}</h2>
                <a href="{{ route('debtors.index') }}" class="text-xs font-medium text-stone-500 hover:text-stone-800">{{ __('Open debtors report') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-stone-100 bg-stone-50/80 text-xs text-stone-500">
                        <tr>
                            <th class="px-4 py-2 font-medium">{{ __('Student') }}</th>
                            <th class="hidden px-4 py-2 font-medium sm:table-cell">{{ __('Class') }}</th>
                            <th class="px-4 py-2 text-right font-medium">{{ __('Balance') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($topDebtors as $row)
                            <tr>
                                <td class="px-4 py-2.5">
                                    <a href="{{ route('students.show', $row['student']) }}" class="font-medium text-stone-800 hover:underline">{{ $row['student']->name }}</a>
                                    <div class="text-xs text-stone-500 sm:hidden">{{ $row['student']->schoolClass?->name }}</div>
                                </td>
                                <td class="hidden px-4 py-2.5 text-stone-600 sm:table-cell">{{ $row['student']->schoolClass?->name }}@if($row['student']->schoolClass?->section) — {{ $row['student']->schoolClass->section }}@endif</td>
                                <td class="px-4 py-2.5 text-right tabular-nums font-medium text-amber-900/90">{{ cedis($row['balance']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-stone-500">{{ __('No outstanding balances on record.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endcan
@endsection
