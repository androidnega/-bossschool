@extends('layouts.app')

@section('title', __('Owner dashboard'))

@section('header-title', __('Proprietor dashboard'))

@section('content')
    <div class="flex flex-col gap-3 border-b border-emerald-200/60 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-emerald-950">{{ __('School performance') }}</h1>
            <p class="mt-1 text-sm text-emerald-900/80">{{ __('Revenue, enrolment, attendance, and subscription health.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('students.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-800"><i class="fa-solid fa-user-plus" aria-hidden="true"></i>{{ __('Add student') }}</a>
            <a href="{{ route('payments.create') }}" class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-white px-3 py-2 text-sm font-medium text-emerald-900 hover:bg-emerald-50"><i class="fa-solid fa-receipt" aria-hidden="true"></i>{{ __('Record payment') }}</a>
            <a href="{{ route('debtors.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-user-clock" aria-hidden="true"></i>{{ __('Debtors') }}</a>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-file-lines" aria-hidden="true"></i>{{ __('Reports') }}</a>
            <a href="{{ route('staff.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-id-badge" aria-hidden="true"></i>{{ __('Staff') }}</a>
            @can('viewAny', \App\Models\Message::class)
                <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i>{{ __('Notices') }}</a>
            @endcan
        </div>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-dash-card icon="fa-solid fa-user-graduate" :label="__('Students')" :value="number_format($studentCount)" variant="students" :hint="__('Active: :n', ['n' => number_format($activeStudentCount)])" />
        <x-dash-card icon="fa-solid fa-money-bill-wave" :label="__('Fees collected')" :value="cedis($feesCollected)" variant="revenue" />
        <x-dash-card icon="fa-solid fa-scale-unbalanced" :label="__('Outstanding')" :value="cedis($outstandingFees)" variant="debtors" />
        <x-dash-card icon="fa-solid fa-user-clock" :label="__('Debtors')" :value="number_format($debtorsCount)" variant="debtors" />
    </div>

    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <x-dash-card icon="fa-solid fa-calendar-day" :label="__('Payments today')" :value="number_format($paymentsToday)" variant="revenue" />
        <x-dash-card icon="fa-solid fa-id-badge" :label="__('Staff')" :value="number_format($staffCount)" variant="staff" />
        <x-dash-card icon="fa-solid fa-calendar-check" :label="__('Attendance today')" :value="number_format($attendanceToday)" variant="attendance" />
        <x-dash-card icon="fa-solid fa-file-signature" :label="__('Subscription')" :value="$subscription ? ucfirst($subscription->status) : '—'" variant="results" :hint="$subscription ? __('End date').': '.($subscription->end_date?->toDateString() ?? '—') : __('No active row')" />
    </div>

    <div class="mt-10 grid gap-8 lg:grid-cols-2">
        <div>
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-chart-line text-emerald-700" aria-hidden="true"></i>{{ __('Student growth (6 months)') }}</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @forelse ($growth as $ym => $c)
                    <div class="dash-card-neutral min-w-[6.5rem] rounded-xl border border-black/[0.06] px-3 py-2 text-center">
                        <p class="text-xs text-slate-500">{{ $ym }}</p>
                        <p class="text-lg font-semibold text-slate-900">{{ $c }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-600">{{ __('No enrolment data in range.') }}</p>
                @endforelse
            </div>
        </div>
        <div>
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-receipt text-teal-700" aria-hidden="true"></i>{{ __('Recent payments') }}</h2>
            <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-2">{{ __('Student') }}</th>
                            <th class="px-4 py-2">{{ __('Amount') }}</th>
                            <th class="px-4 py-2">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentPayments as $p)
                            <tr>
                                <td class="px-4 py-2">{{ $p->student?->name }}</td>
                                <td class="px-4 py-2 font-medium text-emerald-800">{{ number_format((float) $p->amount, 2) }}</td>
                                <td class="px-4 py-2 text-slate-600">{{ $p->date }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">{{ __('No payments yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-10">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-arrow-trend-down text-amber-700" aria-hidden="true"></i>{{ __('Top debtors') }}</h2>
        <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2">{{ __('Student') }}</th>
                        <th class="px-4 py-2">{{ __('Class') }}</th>
                        <th class="px-4 py-2">{{ __('Balance') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($topDebtors as $row)
                        <tr>
                            <td class="px-4 py-2 font-medium text-slate-900">{{ $row['student']->name }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $row['student']->schoolClass?->name }}</td>
                            <td class="px-4 py-2 font-semibold text-amber-800">{{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">{{ __('No outstanding balances.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-10">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-bullhorn text-emerald-800" aria-hidden="true"></i>{{ __('Latest notices & reminders') }}</h2>
        <ul class="mt-3 space-y-2">
            @forelse ($recentNotices as $m)
                <li class="dash-card-messages rounded-xl border border-black/[0.06] p-3 text-sm text-slate-800">
                    <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">{{ __('Notice') }}</p>
                    @if ($m->title)
                        <p class="mt-1 font-medium text-slate-900">{{ $m->title }}</p>
                    @endif
                    <p class="text-xs text-slate-500">{{ $m->sent_at?->diffForHumans() }} · {{ $m->sender?->name ?? __('System') }} · {{ $m->notice_kind ?? $m->channel ?? '—' }} · {{ $m->status }}</p>
                    <p class="mt-1 text-xs text-slate-600">{{ $m->audienceDisplay() }}</p>
                    <p class="mt-1">{{ \Illuminate\Support\Str::limit($m->content, 140) }}</p>
                </li>
            @empty
                <li class="text-sm text-slate-600">{{ __('No notices yet.') }}</li>
            @endforelse
        </ul>
    </div>

    <div class="mt-10">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-user-plus text-blue-700" aria-hidden="true"></i>{{ __('Recent admissions') }}</h2>
        <ul class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
            @foreach ($recentAdmissions as $s)
                <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                    <span class="font-medium text-slate-900">{{ $s->name }}</span>
                    <span class="text-slate-600">{{ $s->schoolClass?->name }}</span>
                    <span class="text-slate-500">{{ $s->admission_date?->toDateString() }}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
