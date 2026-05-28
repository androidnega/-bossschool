@extends('layouts.app')

@section('title', __('Parent home'))

@section('header-title', __('Family overview'))

@section('content')
    <div class="flex flex-col gap-3 border-b border-emerald-200/50 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-emerald-950">{{ __('Your children') }}</h1>
            <p class="mt-1 text-sm text-emerald-900/75">{{ __('Linked learners only — fees, academics, and notices.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2 text-sm">
            <span class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50/50 px-3 py-2 text-emerald-900"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>{{ __('Other students are hidden') }}</span>
        </div>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        @forelse ($children as $child)
            @php
                $bal = $balances[$child->id] ?? 0;
                $sum = $attendanceSummaries[$child->id] ?? ['present' => 0, 'total' => 0];
                $lr = $latestResults[$child->id] ?? collect();
            @endphp
            <div class="dash-card-students flex flex-col rounded-xl border border-black/[0.06] p-5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h2 class="flex items-center gap-2 text-lg font-semibold text-emerald-950"><i class="fa-solid fa-child text-emerald-700" aria-hidden="true"></i>{{ $child->name }}</h2>
                        <p class="mt-1 text-sm text-slate-600"><i class="fa-solid fa-users-between-lines me-1 text-slate-400" aria-hidden="true"></i>{{ $child->schoolClass?->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase text-slate-500">{{ __('Balance') }}</p>
                        <p class="text-xl font-semibold text-amber-800">{{ cedis($bal) }}</p>
                    </div>
                </div>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    <div class="rounded-lg bg-white/70 px-3 py-2 text-sm ring-1 ring-black/[0.05]">
                        <p class="text-xs text-slate-500">{{ __('Attendance (14d)') }}</p>
                        <p class="font-semibold text-slate-900">{{ $sum['present'] }}/{{ $sum['total'] }} <span class="text-slate-500 font-normal">{{ __('present') }}</span></p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('portal.parent.child', $child) }}" class="inline-flex flex-1 items-center justify-center gap-1 rounded-lg bg-emerald-700 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-800 sm:flex-none"><i class="fa-solid fa-id-card" aria-hidden="true"></i>{{ __('Profile') }}</a>
                        <a href="{{ route('portal.parent.child.report-card', $child) }}" class="inline-flex flex-1 items-center justify-center gap-1 rounded-lg border border-emerald-300 px-3 py-2 text-xs font-medium text-emerald-900 hover:bg-emerald-50 sm:flex-none"><i class="fa-solid fa-scroll" aria-hidden="true"></i>{{ __('Results') }}</a>
                    </div>
                </div>
                @if ($lr->isNotEmpty())
                    <div class="mt-4 border-t border-black/[0.06] pt-3">
                        <p class="text-xs font-semibold uppercase text-slate-500">{{ __('Latest results') }}</p>
                        <ul class="mt-2 space-y-1 text-sm text-slate-800">
                            @foreach ($lr as $r)
                                <li class="flex justify-between gap-2"><span>{{ $r->subject?->name }}</span><span class="font-medium">{{ ($r->class_test ?? 0) + ($r->midterm ?? 0) + ($r->exam ?? 0) }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @empty
            <div class="dash-card-danger rounded-xl border border-rose-200 p-6 text-center text-sm text-rose-900">
                <i class="fa-solid fa-circle-info mb-2 text-2xl" aria-hidden="true"></i>
                <p>{{ __('No children linked yet. Ask the school to connect your account.') }}</p>
            </div>
        @endforelse
    </div>

    @if ($recentPayments->isNotEmpty())
        <div class="mt-10">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-receipt text-emerald-700" aria-hidden="true"></i>{{ __('Recent payments') }}</h2>
            <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-2">{{ __('Child') }}</th>
                            <th class="px-4 py-2">{{ __('Amount') }}</th>
                            <th class="px-4 py-2">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recentPayments as $p)
                            <tr>
                                <td class="px-4 py-2">{{ $p->student?->name }}</td>
                                <td class="px-4 py-2 font-medium text-emerald-800">{{ cedis((float) $p->amount) }}</td>
                                <td class="px-4 py-2 text-slate-600">{{ $p->date }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($messages->isNotEmpty())
        <div class="mt-10">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i class="fa-solid fa-bullhorn text-pink-700" aria-hidden="true"></i>{{ __('Notices for you') }}</h2>
            <ul class="mt-3 space-y-3">
                @foreach ($messages as $m)
                    <li class="dash-card-messages rounded-xl border border-black/[0.06] p-4 text-sm text-slate-800">
                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">{{ __('Notice') }}</p>
                        @if ($m->title)
                            <p class="mt-1 font-medium text-slate-900">{{ $m->title }}</p>
                        @endif
                        <p class="text-xs text-slate-500"><i class="fa-regular fa-clock me-1" aria-hidden="true"></i>{{ $m->sent_at?->diffForHumans() }} · {{ $m->sender?->name ?? __('School') }} · {{ $m->audienceDisplay() }}</p>
                        <p class="mt-2">{{ $m->content }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
