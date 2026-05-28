<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice {{ $invoice->invoice_number }}</title>
<style>
    @page { margin: 22mm; }
    body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
    h1 { margin: 0; font-size: 18px; }
    .header { display: flex; justify-content: space-between; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    th, td { padding: 6px 8px; border-bottom: 1px solid #f1f1f1; vertical-align: top; }
    th { text-align: left; background: #f9fafb; }
    .right { text-align: right; }
    .totals { width: 50%; margin-left: 50%; margin-top: 16px; }
    .totals td { padding: 4px 8px; }
    .label { color: #6b7280; font-size: 11px; }
</style>
</head>
<body>
<div class="header">
    <div>
        <h1>{{ $school?->name ?? config('app.name') }}</h1>
        <div class="label">{{ $school?->address }}</div>
        <div class="label">{{ $school?->phone }}</div>
    </div>
    <div style="text-align:right">
        <h1>{{ __('Invoice') }}</h1>
        <div class="label">{{ $invoice->invoice_number }}</div>
        <div class="label">{{ __('Issued') }}: {{ $invoice->issued_at?->format('F j, Y') ?? '—' }}</div>
        <div class="label">{{ __('Due') }}: {{ $invoice->due_date?->format('F j, Y') ?? '—' }}</div>
    </div>
</div>

<table>
    <tr>
        <td>
            <div class="label">{{ __('Billed to') }}</div>
            <div>{{ $invoice->student?->name }}</div>
            <div class="label">{{ $invoice->student?->schoolClass?->name }}@if($invoice->student?->schoolClass?->section) — {{ $invoice->student->schoolClass->section }}@endif</div>
        </td>
        <td style="text-align:right">
            <div class="label">{{ __('Term / Year') }}</div>
            <div>{{ $invoice->term?->name ?? '—' }} · {{ $invoice->academicYear?->name ?? '—' }}</div>
            <div class="label">{{ __('Status') }}: {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</div>
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>{{ __('Description') }}</th>
            <th>{{ __('Category') }}</th>
            <th class="right">{{ __('Qty') }}</th>
            <th class="right">{{ __('Unit') }}</th>
            <th class="right">{{ __('Total') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td>{{ $item->category }}</td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">{{ cedis($item->unit_amount) }}</td>
                <td class="right">{{ cedis($item->total_amount) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr><td class="label">{{ __('Subtotal') }}</td><td class="right">{{ cedis($invoice->subtotal) }}</td></tr>
    <tr><td class="label">{{ __('Discounts') }}</td><td class="right">-{{ cedis($invoice->discount_total) }}</td></tr>
    <tr><td class="label">{{ __('Waivers') }}</td><td class="right">-{{ cedis($invoice->waiver_total) }}</td></tr>
    <tr><td><strong>{{ __('Amount due') }}</strong></td><td class="right"><strong>{{ cedis($invoice->amount_due) }}</strong></td></tr>
    <tr><td class="label">{{ __('Paid') }}</td><td class="right">{{ cedis($invoice->amount_paid) }}</td></tr>
    <tr><td><strong>{{ __('Balance') }}</strong></td><td class="right"><strong>{{ cedis($invoice->balance) }}</strong></td></tr>
</table>

@if($invoice->notes)
    <p class="label" style="margin-top:24px">{{ $invoice->notes }}</p>
@endif
</body>
</html>
