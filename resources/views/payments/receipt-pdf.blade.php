<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Receipt {{ $payment->receipt_id }}</title>
<style>
    @page { margin: 12mm; }
    body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; }
    h1 { margin: 0; font-size: 15px; }
    .label { color: #6b7280; font-size: 10px; }
    table { width: 100%; border-collapse: collapse; }
    td { padding: 4px 4px; vertical-align: top; }
    .row { display:flex; justify-content: space-between; }
    .sig { margin-top: 40px; border-top: 1px solid #999; width: 200px; padding-top: 4px; font-size: 10px; color: #555; }
    .reversed { color: #b91c1c; font-weight: bold; }
</style>
</head>
<body>
<div style="text-align:center; border-bottom:1px solid #e5e7eb; padding-bottom:8px">
    <h1>{{ $school?->name ?? config('app.name') }}</h1>
    <div class="label">{{ $school?->address }} · {{ $school?->phone }}</div>
    <div style="margin-top:4px; font-size:13px"><strong>{{ __('Payment receipt') }}</strong></div>
</div>

<table style="margin-top:8px">
    <tr>
        <td class="label">{{ __('Receipt #') }}</td>
        <td><strong>{{ $payment->receipt_id }}</strong></td>
        <td class="label">{{ __('Date') }}</td>
        <td>{{ \Illuminate\Support\Carbon::parse($payment->date)->format('F j, Y') }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Student') }}</td>
        <td>{{ $payment->student?->name }}</td>
        <td class="label">{{ __('Admission #') }}</td>
        <td>{{ $payment->student?->admission_number ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Class') }}</td>
        <td>{{ $payment->student?->schoolClass?->name }}@if($payment->student?->schoolClass?->section) — {{ $payment->student->schoolClass->section }}@endif</td>
        <td class="label">{{ __('Invoice') }}</td>
        <td>{{ $payment->invoice?->invoice_number ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Channel') }}</td>
        <td>{{ ucfirst($payment->payment_channel) }}@if($payment->provider && $payment->provider !== 'manual') · {{ ucfirst($payment->provider) }}@endif</td>
        <td class="label">{{ __('Reference') }}</td>
        <td>{{ $payment->payment_reference ?: $payment->reference ?: '—' }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Received by') }}</td>
        <td>{{ $payment->receiver?->name ?? '—' }}</td>
        <td class="label">{{ __('Status') }}</td>
        <td>
            @if($payment->status === \App\Models\Payment::STATUS_REVERSED)
                <span class="reversed">{{ __('REVERSED') }}</span>
            @else
                {{ ucfirst($payment->status) }}
            @endif
        </td>
    </tr>
</table>

<table style="margin-top:16px; border:1px solid #e5e7eb;">
    <tr>
        <td style="background:#f9fafb"><strong>{{ __('Amount paid') }}</strong></td>
        <td style="text-align:right; font-size:14px"><strong>{{ cedis($payment->amount) }}</strong></td>
    </tr>
    @if($payment->invoice)
        <tr>
            <td class="label">{{ __('Invoice balance after this payment') }}</td>
            <td style="text-align:right">{{ cedis($payment->invoice->balance) }}</td>
        </tr>
    @endif
</table>

@if($payment->status === \App\Models\Payment::STATUS_REVERSED)
    <p class="reversed" style="margin-top:12px">{{ __('This receipt has been reversed. Reason: :r', ['r' => $payment->reversal_reason]) }}</p>
@endif

<div class="sig">{{ __('Authorised signature') }}</div>
</body>
</html>
