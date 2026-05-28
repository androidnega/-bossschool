<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $student->name }} — {{ __('Report card') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; margin: 0; padding: 0; }
        .page { padding: 18px 22px; }
        .center { text-align: center; }
        .muted { color: #6b7280; }
        .label { color: #6b7280; font-size: 9px; text-transform: uppercase; letter-spacing: 0.04em; }
        .strong { font-weight: 700; }
        h1.school { font-size: 16px; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; color: #1f2937; }
        h2.terminal { font-size: 12px; margin: 4px 0 0; text-transform: uppercase; letter-spacing: 0.08em; color: #111827; }
        p.contact { font-size: 10px; margin: 4px 0 0; color: #374151; }
        .header { border-bottom: 1px solid #d1d5db; padding-bottom: 8px; margin-bottom: 10px; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .grid td { padding: 4px 6px; border: 1px solid #e5e7eb; vertical-align: top; }
        .grid td.label-cell { width: 22%; background: #f9fafb; color: #4b5563; font-size: 9px; }
        table.results { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10px; }
        table.results th, table.results td { border: 1px solid #d1d5db; padding: 4px 6px; }
        table.results th { background: #f3f4f6; text-align: left; }
        table.results td.num { text-align: right; }
        table.attend { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 10px; }
        table.attend td { border: 1px solid #d1d5db; padding: 4px 6px; }
        .remark-box { border: 1px solid #d1d5db; padding: 6px; margin-top: 6px; min-height: 26px; }
        .signature-row { margin-top: 30px; }
        .signature-row td { width: 50%; vertical-align: top; padding: 0 6px; }
        .sig-line { border-top: 1px dotted #6b7280; padding-top: 4px; min-height: 22px; }
        .small { font-size: 9px; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <table>
            <tr>
                @if ($school?->logo && file_exists(public_path('storage/'.$school->logo)))
                    <td style="width: 64px;">
                        <img src="{{ public_path('storage/'.$school->logo) }}" alt="logo" style="height: 56px; width: 56px;">
                    </td>
                @endif
                <td class="center">
                    <h1 class="school">{{ $school?->name ?? app('currentTenant')->name }}</h1>
                    @if ($school?->motto)
                        <p class="muted small" style="margin: 2px 0 0; font-style: italic;">"{{ $school->motto }}"</p>
                    @endif
                    <p class="contact">
                        @if ($school?->address) {{ $school->address }} @endif
                        @if ($school?->phone) · {{ $school->phone }} @endif
                        @if ($school?->email) · {{ $school->email }} @endif
                    </p>
                    <p class="contact small">
                        @if ($school?->ges_region) {{ __('Region') }}: {{ $school->ges_region }} @endif
                        @if ($school?->ges_district) · {{ __('District') }}: {{ $school->ges_district }} @endif
                        @if ($school?->ges_circuit) · {{ __('Circuit') }}: {{ $school->ges_circuit }} @endif
                        @if ($school?->school_code) · {{ __('Code') }}: {{ $school->school_code }} @endif
                    </p>
                    <h2 class="terminal">{{ __('Terminal report') }}</h2>
                </td>
            </tr>
        </table>
    </div>

    <table class="grid">
        <tr>
            <td class="label-cell">{{ __('Student') }}</td>
            <td class="strong">{{ $student->name }}</td>
            <td class="label-cell">{{ __('Admission no.') }}</td>
            <td>{{ $student->admission_no ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label-cell">{{ __('Class') }}</td>
            <td>{{ $student->schoolClass?->name }}@if($student->schoolClass?->section) ({{ $student->schoolClass->section }})@endif</td>
            <td class="label-cell">{{ __('Sex') }}</td>
            <td style="text-transform: capitalize">{{ $student->gender ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label-cell">{{ __('Academic year') }}</td>
            <td>{{ $year?->name ?? '—' }}</td>
            <td class="label-cell">{{ __('Term') }}</td>
            <td>{{ $term?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label-cell">{{ __('Position in class') }}</td>
            <td>@if ($position) {{ $position['rank'] }} / {{ $position['size'] }} @else — @endif</td>
            <td class="label-cell">{{ __('Class size') }}</td>
            <td>{{ $meta?->class_size ?? $position['size'] ?? '—' }}</td>
        </tr>
    </table>

    <table class="attend">
        <tr>
            <td class="label">{{ __('Days school opened') }}</td>
            <td class="strong">{{ $attendance['opened'] }}</td>
            <td class="label">{{ __('Days present') }}</td>
            <td class="strong">{{ $attendance['present'] }}</td>
            <td class="label">{{ __('Days absent') }}</td>
            <td class="strong">{{ $attendance['absent'] }}</td>
            <td class="label">{{ __('Late') }}</td>
            <td class="strong">{{ $attendance['late'] }}</td>
            <td class="label">{{ __('Excused') }}</td>
            <td class="strong">{{ $attendance['excused'] }}</td>
        </tr>
    </table>

    <table class="results">
        <thead>
            <tr>
                <th>{{ __('Subject') }}</th>
                <th class="num">{{ __('Class test') }}</th>
                <th class="num">{{ __('Mid-term') }}</th>
                <th class="num">{{ __('Exam') }}</th>
                <th class="num">{{ __('Total') }}</th>
                <th>{{ __('Grade') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($results as $res)
            <tr>
                <td>{{ $res->subject?->name }}</td>
                <td class="num">{{ $res->class_test !== null ? number_format((float) $res->class_test, 2) : '—' }}</td>
                <td class="num">{{ $res->midterm !== null ? number_format((float) $res->midterm, 2) : '—' }}</td>
                <td class="num">{{ $res->exam !== null ? number_format((float) $res->exam, 2) : '—' }}</td>
                <td class="num strong">{{ number_format((float) $res->total, 2) }}</td>
                <td class="strong">{{ $res->grade }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="center muted" style="padding: 12px;">{{ __('No results recorded for this term.') }}</td></tr>
        @endforelse
        </tbody>
        @if ($results->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right" class="strong">{{ __('Total / Average') }}</td>
                    <td class="num strong">{{ number_format($total, 2) }}</td>
                    <td class="strong">{{ number_format($average, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <table style="width:100%; border-collapse: collapse; margin-top: 8px;">
        <tr>
            <td style="width: 33%; padding: 4px;">
                <span class="label">{{ __('Conduct') }}</span><br>
                <span class="strong">{{ $meta?->conduct ?? '—' }}</span>
            </td>
            <td style="width: 33%; padding: 4px;">
                <span class="label">{{ __('Attitude') }}</span><br>
                <span class="strong">{{ $meta?->attitude ?? '—' }}</span>
            </td>
            <td style="width: 33%; padding: 4px;">
                <span class="label">{{ __('Interest') }}</span><br>
                <span class="strong">{{ $meta?->interest ?? '—' }}</span>
            </td>
        </tr>
    </table>

    <div class="remark-box">
        <div class="label">{{ __('Class teacher’s remarks') }}</div>
        <div>{{ $meta?->class_teacher_remark ?? __('Not yet entered.') }}</div>
    </div>
    <div class="remark-box">
        <div class="label">{{ __('Headteacher’s remarks') }}</div>
        <div>{{ $meta?->head_teacher_remark ?? __('Not yet entered.') }}</div>
    </div>

    <table style="width:100%; border-collapse: collapse; margin-top: 8px;">
        <tr>
            <td style="width: 33%; padding: 4px;">
                <span class="label">{{ __('Next term fee') }}</span><br>
                <span class="strong">{{ $meta?->next_term_fee !== null ? cedis((float) $meta->next_term_fee) : '—' }}</span>
            </td>
            <td style="width: 33%; padding: 4px;">
                <span class="label">{{ __('Vacation date') }}</span><br>
                <span class="strong">{{ $meta?->vacation_date?->toFormattedDateString() ?? '—' }}</span>
            </td>
            <td style="width: 33%; padding: 4px;">
                <span class="label">{{ __('Reopening date') }}</span><br>
                <span class="strong">{{ $meta?->reopening_date?->toFormattedDateString() ?? '—' }}</span>
            </td>
        </tr>
    </table>

    <table class="signature-row">
        <tr>
            <td>
                <div class="sig-line">{{ $meta?->class_teacher_signature ?? '' }}</div>
                <p class="label">{{ __('Class teacher’s signature') }}</p>
            </td>
            <td>
                <div class="sig-line">{{ $meta?->head_teacher_signature ?? '' }}</div>
                <p class="label">{{ __('Headteacher’s signature') }}</p>
                @if ($school?->head_teacher_name)
                    <p class="small muted">{{ $school->head_teacher_name }}</p>
                @endif
            </td>
        </tr>
    </table>
</div>
</body>
</html>
