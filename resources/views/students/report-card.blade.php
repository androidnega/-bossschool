<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BossSchool | {{ __('Report card') }} — {{ $student->name }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @include('layouts.partials.head-assets')
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
        .signature-line { border-top: 1px dotted #6b7280; padding-top: 6px; min-height: 36px; }
    </style>
</head>
<body class="min-h-screen bg-page-soft font-sans text-gray-900 antialiased">
    <div class="no-print mx-auto max-w-4xl border-b border-gray-200 bg-page px-4 py-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ url()->previous() }}" class="text-sm font-medium text-secondary hover:text-primary">← {{ __('Back') }}</a>
            <form method="GET" action="" class="flex flex-wrap items-end gap-2 text-sm">
                <div>
                    <label for="year_sel" class="block text-xs text-gray-600">{{ __('Year') }}</label>
                    <select id="year_sel" name="academic_year_id" onchange="this.form.submit()" class="rounded-md border border-gray-300 bg-page px-2 py-1 text-sm">
                        @foreach ($years as $y)
                            <option value="{{ $y->id }}" @selected($year && (int) $year->id === (int) $y->id)>{{ $y->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="term_sel" class="block text-xs text-gray-600">{{ __('Term') }}</label>
                    <select id="term_sel" name="term_id" onchange="this.form.submit()" class="rounded-md border border-gray-300 bg-page px-2 py-1 text-sm">
                        @foreach ($terms as $t)
                            <option value="{{ $t->id }}" @selected($term && (int) $term->id === (int) $t->id)>{{ $t->name }} · {{ $t->academicYear?->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <div class="flex gap-2">
                @if (Route::has('students.report-card.pdf'))
                    <a href="{{ route('students.report-card.pdf', ['student' => $student, 'academic_year_id' => $year?->id, 'term_id' => $term?->id]) }}" class="rounded-md border border-gray-300 bg-page px-3 py-1.5 text-sm text-gray-700 hover:bg-page-soft">{{ __('Download PDF') }}</a>
                @endif
                <button type="button" onclick="window.print()" class="rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-white hover:bg-primary/95">{{ __('Print') }}</button>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-4xl bg-page p-6 print:shadow-none md:p-10">
        <header class="border-b border-gray-200 pb-4">
            <div class="flex flex-wrap items-start gap-4">
                @if ($school?->logo)
                    <img src="{{ asset('storage/'.$school->logo) }}" alt="logo" class="h-16 w-16 rounded-md border border-gray-200 object-contain">
                @endif
                <div class="flex-1 text-center">
                    <p class="text-lg font-bold uppercase tracking-wide text-primary">{{ $school?->name ?? app('currentTenant')->name }}</p>
                    @if ($school?->motto)
                        <p class="text-xs italic text-gray-600">"{{ $school->motto }}"</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-700">
                        @if ($school?->address) {{ $school->address }} @endif
                        @if ($school?->phone) · {{ $school->phone }} @endif
                        @if ($school?->email) · {{ $school->email }} @endif
                    </p>
                    <p class="text-xs text-gray-600">
                        @if ($school?->ges_region) {{ __('Region') }}: {{ $school->ges_region }} @endif
                        @if ($school?->ges_district) · {{ __('District') }}: {{ $school->ges_district }} @endif
                        @if ($school?->ges_circuit) · {{ __('Circuit') }}: {{ $school->ges_circuit }} @endif
                        @if ($school?->school_code) · {{ __('Code') }}: {{ $school->school_code }} @endif
                    </p>
                    <p class="mt-3 text-base font-semibold uppercase tracking-wider text-gray-900">{{ __('Terminal report') }}</p>
                </div>
            </div>
        </header>

        <section class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
            <div>
                <p class="text-xs uppercase text-gray-500">{{ __('Student') }}</p>
                <p class="font-semibold text-gray-900">{{ $student->name }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">{{ __('Admission no.') }}</p>
                <p class="font-semibold text-gray-900">{{ $student->admission_no ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">{{ __('Class') }}</p>
                <p class="font-semibold text-gray-900">{{ $student->schoolClass?->name ?? '—' }}@if($student->schoolClass?->section) ({{ $student->schoolClass->section }})@endif</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">{{ __('Sex') }}</p>
                <p class="font-semibold text-gray-900 capitalize">{{ $student->gender ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">{{ __('Academic year') }}</p>
                <p class="font-semibold text-gray-900">{{ $year?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">{{ __('Term') }}</p>
                <p class="font-semibold text-gray-900">{{ $term?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">{{ __('Position in class') }}</p>
                <p class="font-semibold text-gray-900">
                    @if ($position) {{ $position['rank'] }} {{ __('out of') }} {{ $position['size'] }} @else — @endif
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">{{ __('Class size') }}</p>
                <p class="font-semibold text-gray-900">{{ $meta?->class_size ?? $position['size'] ?? '—' }}</p>
            </div>
        </section>

        <section class="mt-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-secondary">{{ __('Attendance summary') }}</h2>
            <div class="mt-2 grid grid-cols-3 gap-3 text-sm sm:grid-cols-5">
                <div class="rounded border border-gray-200 px-3 py-2">
                    <p class="text-xs text-gray-500">{{ __('Days school opened') }}</p>
                    <p class="font-semibold text-gray-900">{{ $attendance['opened'] }}</p>
                </div>
                <div class="rounded border border-emerald-200 bg-emerald-50/60 px-3 py-2">
                    <p class="text-xs text-emerald-700">{{ __('Days present') }}</p>
                    <p class="font-semibold text-emerald-900">{{ $attendance['present'] }}</p>
                </div>
                <div class="rounded border border-amber-200 bg-amber-50/60 px-3 py-2">
                    <p class="text-xs text-amber-700">{{ __('Days absent') }}</p>
                    <p class="font-semibold text-amber-900">{{ $attendance['absent'] }}</p>
                </div>
                <div class="rounded border border-gray-200 px-3 py-2">
                    <p class="text-xs text-gray-500">{{ __('Late') }}</p>
                    <p class="font-semibold text-gray-900">{{ $attendance['late'] }}</p>
                </div>
                <div class="rounded border border-gray-200 px-3 py-2">
                    <p class="text-xs text-gray-500">{{ __('Excused') }}</p>
                    <p class="font-semibold text-gray-900">{{ $attendance['excused'] }}</p>
                </div>
            </div>
        </section>

        <section class="mt-6 overflow-hidden rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-page-soft">
                    <tr>
                        <th class="px-3 py-2 font-medium text-gray-700">{{ __('Subject') }}</th>
                        <th class="px-3 py-2 font-medium text-gray-700 text-right">{{ __('Class test') }}</th>
                        <th class="px-3 py-2 font-medium text-gray-700 text-right">{{ __('Mid-term') }}</th>
                        <th class="px-3 py-2 font-medium text-gray-700 text-right">{{ __('Exam') }}</th>
                        <th class="px-3 py-2 font-medium text-gray-700 text-right">{{ __('Total') }}</th>
                        <th class="px-3 py-2 font-medium text-gray-700">{{ __('Grade') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-page">
                    @forelse ($results as $res)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900">{{ $res->subject?->name }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-gray-700">{{ $res->class_test !== null ? number_format((float) $res->class_test, 2) : '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-gray-700">{{ $res->midterm !== null ? number_format((float) $res->midterm, 2) : '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-gray-700">{{ $res->exam !== null ? number_format((float) $res->exam, 2) : '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums font-medium text-gray-900">{{ number_format((float) $res->total, 2) }}</td>
                            <td class="px-3 py-2 font-semibold text-primary">{{ $res->grade }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-6 text-center text-gray-600">{{ __('No results recorded for this term.') }}</td></tr>
                    @endforelse
                </tbody>
                @if ($results->isNotEmpty())
                    <tfoot class="bg-page-soft">
                        <tr>
                            <td class="px-3 py-2 text-right text-sm font-medium text-gray-700" colspan="4">{{ __('Total / Average') }}</td>
                            <td class="px-3 py-2 text-right tabular-nums font-semibold text-gray-900">{{ number_format($total, 2) }}</td>
                            <td class="px-3 py-2 font-semibold text-gray-900">{{ number_format($average, 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </section>

        <section class="mt-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded border border-gray-200 px-3 py-2">
                <p class="text-xs uppercase text-gray-500">{{ __('Conduct') }}</p>
                <p class="font-semibold text-gray-900">{{ $meta?->conduct ?? '—' }}</p>
            </div>
            <div class="rounded border border-gray-200 px-3 py-2">
                <p class="text-xs uppercase text-gray-500">{{ __('Attitude') }}</p>
                <p class="font-semibold text-gray-900">{{ $meta?->attitude ?? '—' }}</p>
            </div>
            <div class="rounded border border-gray-200 px-3 py-2">
                <p class="text-xs uppercase text-gray-500">{{ __('Interest') }}</p>
                <p class="font-semibold text-gray-900">{{ $meta?->interest ?? '—' }}</p>
            </div>
        </section>

        <section class="mt-6 space-y-3">
            <div class="rounded border border-gray-200 px-3 py-2">
                <p class="text-xs uppercase text-gray-500">{{ __('Class teacher’s remarks') }}</p>
                <p class="mt-1 text-gray-900">{{ $meta?->class_teacher_remark ?? __('Not yet entered.') }}</p>
            </div>
            <div class="rounded border border-gray-200 px-3 py-2">
                <p class="text-xs uppercase text-gray-500">{{ __('Headteacher’s remarks') }}</p>
                <p class="mt-1 text-gray-900">{{ $meta?->head_teacher_remark ?? __('Not yet entered.') }}</p>
            </div>
        </section>

        <section class="mt-6 grid gap-3 text-sm sm:grid-cols-3">
            <div>
                <p class="text-xs uppercase text-gray-500">{{ __('Next term fee') }}</p>
                <p class="font-semibold text-gray-900">{{ $meta?->next_term_fee !== null ? cedis((float) $meta->next_term_fee) : '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">{{ __('Vacation date') }}</p>
                <p class="font-semibold text-gray-900">{{ $meta?->vacation_date?->toFormattedDateString() ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500">{{ __('Reopening date') }}</p>
                <p class="font-semibold text-gray-900">{{ $meta?->reopening_date?->toFormattedDateString() ?? '—' }}</p>
            </div>
        </section>

        <section class="mt-10 grid gap-6 sm:grid-cols-2">
            <div>
                <div class="signature-line">{{ $meta?->class_teacher_signature ?? '' }}</div>
                <p class="mt-1 text-xs uppercase text-gray-500">{{ __('Class teacher’s signature') }}</p>
            </div>
            <div>
                <div class="signature-line">{{ $meta?->head_teacher_signature ?? '' }}</div>
                <p class="mt-1 text-xs uppercase text-gray-500">{{ __('Headteacher’s signature') }}</p>
                @if ($school?->head_teacher_name)
                    <p class="text-xs text-gray-600">{{ $school->head_teacher_name }}</p>
                @endif
            </div>
        </section>
    </div>
</body>
</html>
