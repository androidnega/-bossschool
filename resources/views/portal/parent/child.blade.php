@extends('layouts.app')

@section('title', $student->name)

@section('header-title', $student->name)

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-emerald-950">{{ $student->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $student->schoolClass?->name }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('Balance') }}</p>
            <p class="text-2xl font-semibold text-amber-800">{{ cedis($balance) }}</p>
        </div>
    </div>

    <div class="mt-10 grid gap-8 lg:grid-cols-2">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Payment history') }}</h2>
            <ul class="mt-4 divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
                @forelse ($payments as $p)
                    <li class="flex justify-between gap-2 px-4 py-3 text-sm">
                        <span class="text-slate-600">{{ $p->date }} · {{ $p->method }}</span>
                        <span class="font-medium text-emerald-800">{{ cedis((float) $p->amount) }}</span>
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-sm text-slate-500">{{ __('No payments yet.') }}</li>
                @endforelse
            </ul>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Results snapshot') }}</h2>
            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-2">{{ __('Subject') }}</th>
                            <th class="px-3 py-2">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($results as $r)
                            <tr>
                                <td class="px-3 py-2">{{ $r->subject?->name }}</td>
                                <td class="px-3 py-2 font-medium">{{ ($r->class_test ?? 0) + ($r->midterm ?? 0) + ($r->exam ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <a href="{{ route('portal.parent.child.report-card', $student) }}" class="mt-3 inline-flex text-sm font-medium text-emerald-800 hover:text-emerald-950">{{ __('Full report card') }} →</a>
        </div>
    </div>

    <div class="mt-10">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Attendance (recent)') }}</h2>
        <ul class="mt-4 flex flex-wrap gap-2">
            @foreach ($attendance as $a)
                <span class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700">{{ $a->date?->toDateString() }}: {{ $a->status }}</span>
            @endforeach
        </ul>
    </div>
@endsection
