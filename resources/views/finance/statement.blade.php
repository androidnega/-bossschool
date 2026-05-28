@extends('layouts.app')

@section('title', __('Fee statement') . ' · ' . $student->name)

@section('header-title', 'Fees')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-primary">{{ __('Fee statement') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $student->name }} @if($student->schoolClass) · {{ $student->schoolClass->name }}@if($student->schoolClass->section) — {{ $student->schoolClass->section }}@endif @endif</p>
        </div>
        <div class="flex flex-wrap gap-2 text-sm">
            @php
                $pdfRoute = match($pdfContext ?? auth()->user()->role) {
                    'Parent' => route('portal.parent.child.statement.pdf', $student),
                    'Student' => route('portal.student.statement.pdf'),
                    default => route('students.statement.pdf', $student),
                };
            @endphp
            <a href="{{ $pdfRoute }}" class="rounded-md border border-gray-300 bg-page px-3 py-1.5 text-gray-700 hover:bg-page-soft">{{ __('Download PDF') }}</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-5 lg:grid-cols-5">
        @foreach ([
            ['label' => __('Total billed'), 'value' => cedis($totals['billed']), 'class' => 'text-gray-900'],
            ['label' => __('Discounts'), 'value' => '-'.cedis($totals['discounts']), 'class' => 'text-emerald-700'],
            ['label' => __('Waivers'), 'value' => '-'.cedis($totals['waivers']), 'class' => 'text-emerald-700'],
            ['label' => __('Paid'), 'value' => cedis($totals['paid']), 'class' => 'text-emerald-700'],
            ['label' => __('Balance'), 'value' => cedis($totals['balance']), 'class' => 'text-stone-900 font-semibold'],
        ] as $box)
            <div class="rounded-lg border border-gray-200 bg-page p-3">
                <div class="text-xs uppercase text-gray-500">{{ $box['label'] }}</div>
                <div class="mt-1 text-lg tabular-nums {{ $box['class'] }}">{{ $box['value'] }}</div>
            </div>
        @endforeach
    </div>

    @if($totals['arrears'] > 0)
        <div class="mt-4 rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800">
            <strong>{{ __('Arrears from previous terms: :a', ['a' => cedis($totals['arrears'])]) }}</strong>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-page p-4">
            <h2 class="mb-3 text-sm font-semibold text-gray-700">{{ __('Invoices') }}</h2>
            <ul class="divide-y divide-gray-100 text-sm">
                @forelse($invoices as $i)
                    <li class="flex justify-between py-2 gap-3">
                        <span><span class="font-mono">{{ $i->invoice_number }}</span> · {{ $i->term?->name ?? '—' }} · <span class="capitalize">{{ str_replace('_', ' ', $i->status) }}</span></span>
                        <span class="tabular-nums">{{ cedis($i->amount_due) }} / {{ cedis($i->balance) }}</span>
                    </li>
                @empty
                    <li class="py-2 text-gray-500">{{ __('No invoices on file.') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-lg border border-gray-200 bg-page p-4">
            <h2 class="mb-3 text-sm font-semibold text-gray-700">{{ __('Payments') }}</h2>
            <ul class="divide-y divide-gray-100 text-sm">
                @forelse($payments as $p)
                    <li class="flex justify-between py-2 gap-3 {{ $p->status === \App\Models\Payment::STATUS_REVERSED ? 'text-gray-400 line-through' : '' }}">
                        <span><span class="font-mono">{{ $p->receipt_id }}</span> · {{ \Illuminate\Support\Carbon::parse($p->date)->format('M j, Y') }} · {{ ucfirst($p->payment_channel) }}</span>
                        <span class="tabular-nums">{{ cedis($p->amount) }}</span>
                    </li>
                @empty
                    <li class="py-2 text-gray-500">{{ __('No payments yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    @if($adjustments->isNotEmpty())
        <div class="mt-6 rounded-lg border border-gray-200 bg-page p-4">
            <h2 class="mb-3 text-sm font-semibold text-gray-700">{{ __('Adjustments') }}</h2>
            <ul class="divide-y divide-gray-100 text-sm">
                @foreach($adjustments as $adj)
                    <li class="flex justify-between py-2 gap-3">
                        <span class="capitalize">{{ $adj->type }} · {{ $adj->description }} <span class="ml-1 text-xs text-gray-500">[{{ $adj->status }}]</span></span>
                        <span class="tabular-nums text-emerald-700">-{{ cedis($adj->amount) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
