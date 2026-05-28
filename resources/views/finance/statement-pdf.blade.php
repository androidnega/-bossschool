<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Statement - {{ $student->name }}</title>
<style>
    @page { margin: 20mm; }
    body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; }
    h1 { margin: 0; font-size: 16px; }
    .label { color: #6b7280; font-size: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { padding: 5px 6px; border-bottom: 1px solid #f1f1f1; }
    th { text-align: left; background: #f9fafb; }
    .right { text-align: right; }
    .totals { width: 50%; margin-left: 50%; margin-top: 12px; }
    .totals td { padding: 3px 6px; }
</style>
</head>
<body>
<div style="display:flex; justify-content:space-between; border-bottom:1px solid #e5e7eb; padding-bottom:8px">
    <div>
        <h1>{{ $school?->name ?? config('app.name') }}</h1>
        <div class="label">{{ $school?->address }}</div>
        <div class="label">{{ $school?->phone }}</div>
    </div>
    <div style="text-align:right">
        <h1>{{ __('Fee statement') }}</h1>
        <div class="label">{{ now()->format('F j, Y') }}</div>
    </div>
</div>

<table style="margin-top:12px">
    <tr>
        <td>
            <div class="label">{{ __('Student') }}</div>
            <strong>{{ $student->name }}</strong>
            <div class="label">{{ $student->schoolClass?->name }}@if($student->schoolClass?->section) — {{ $student->schoolClass->section }}@endif</div>
        </td>
        <td style="text-align:right">
            <div class="label">{{ __('Admission #') }}</div>
            <div>{{ $student->admission_number ?? '—' }}</div>
        </td>
    </tr>
</table>

<h3>{{ __('Invoices') }}</h3>
<table>
    <thead><tr><th>{{ __('Invoice') }}</th><th>{{ __('Term') }}</th><th class="right">{{ __('Subtotal') }}</th><th class="right">{{ __('Adjustments') }}</th><th class="right">{{ __('Due') }}</th><th class="right">{{ __('Paid') }}</th><th class="right">{{ __('Balance') }}</th></tr></thead>
    <tbody>
        @foreach($invoices as $i)
            <tr>
                <td>{{ $i->invoice_number }}</td>
                <td>{{ $i->term?->name }}</td>
                <td class="right">{{ cedis($i->subtotal) }}</td>
                <td class="right">-{{ cedis($i->discount_total + $i->waiver_total) }}</td>
                <td class="right">{{ cedis($i->amount_due) }}</td>
                <td class="right">{{ cedis($i->amount_paid) }}</td>
                <td class="right">{{ cedis($i->balance) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h3>{{ __('Payments') }}</h3>
<table>
    <thead><tr><th>{{ __('Receipt') }}</th><th>{{ __('Date') }}</th><th>{{ __('Channel') }}</th><th class="right">{{ __('Amount') }}</th><th>{{ __('Status') }}</th></tr></thead>
    <tbody>
        @foreach($payments as $p)
            <tr>
                <td>{{ $p->receipt_id }}</td>
                <td>{{ \Illuminate\Support\Carbon::parse($p->date)->format('M j, Y') }}</td>
                <td>{{ ucfirst($p->payment_channel) }}</td>
                <td class="right">{{ cedis($p->amount) }}</td>
                <td>{{ ucfirst($p->status) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr><td>{{ __('Billed') }}</td><td class="right">{{ cedis($totals['billed']) }}</td></tr>
    <tr><td>{{ __('Discounts') }}</td><td class="right">-{{ cedis($totals['discounts']) }}</td></tr>
    <tr><td>{{ __('Waivers') }}</td><td class="right">-{{ cedis($totals['waivers']) }}</td></tr>
    <tr><td>{{ __('Paid') }}</td><td class="right">{{ cedis($totals['paid']) }}</td></tr>
    <tr><td><strong>{{ __('Balance') }}</strong></td><td class="right"><strong>{{ cedis($totals['balance']) }}</strong></td></tr>
    @if($totals['arrears'] > 0)
        <tr><td>{{ __('of which arrears') }}</td><td class="right">{{ cedis($totals['arrears']) }}</td></tr>
    @endif
</table>
</body>
</html>
