@extends('layouts.app')

@section('title', __('School settings'))
@section('header-title', __('School-wide settings'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('settings.tenant.update') }}" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Default pass mark') }}</span>
                <input type="number" min="0" max="100" name="default_pass_mark" value="{{ old('default_pass_mark', $values['default_pass_mark']) }}" class="w-full rounded-md border border-slate-300 px-2 py-1.5" />
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Default SMS provider') }}</span>
                <select name="default_sms_provider" class="w-full rounded-md border border-slate-300 px-2 py-1.5">
                    @foreach(['log','hubtel','mnotify','arkesel','twilio'] as $p)
                        <option value="{{ $p }}" @selected(($values['default_sms_provider'] ?? '') === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </label>

            <label class="inline-flex items-center gap-2 text-sm sm:col-span-1">
                <input type="checkbox" name="parent_online_payment_enabled" value="1" @checked(! empty($values['parent_online_payment_enabled'])) class="rounded border-slate-300" />
                <span>{{ __('Enable parent online payments') }}</span>
            </label>

            <label class="inline-flex items-center gap-2 text-sm sm:col-span-1">
                <input type="checkbox" name="student_online_payment_enabled" value="1" @checked(! empty($values['student_online_payment_enabled'])) class="rounded border-slate-300" />
                <span>{{ __('Enable student online payments') }}</span>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Default report-card grading mode') }}</span>
                <select name="default_report_card_grading_mode" class="w-full rounded-md border border-slate-300 px-2 py-1.5">
                    @foreach(['WAEC','Cambridge','Custom'] as $g)
                        <option value="{{ $g }}" @selected(($values['default_report_card_grading_mode'] ?? '') === $g)>{{ $g }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Sender ID (alphanumeric, ≤ 11)') }}</span>
                <input type="text" maxlength="11" name="sender_id" value="{{ old('sender_id', $values['sender_id']) }}" class="w-full rounded-md border border-slate-300 px-2 py-1.5" />
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Default invoice due days') }}</span>
                <input type="number" min="0" max="120" name="default_invoice_due_days" value="{{ old('default_invoice_due_days', $values['default_invoice_due_days']) }}" class="w-full rounded-md border border-slate-300 px-2 py-1.5" />
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Attendance days per term') }}</span>
                <input type="number" min="1" max="200" name="default_attendance_days_per_term" value="{{ old('default_attendance_days_per_term', $values['default_attendance_days_per_term']) }}" class="w-full rounded-md border border-slate-300 px-2 py-1.5" />
            </label>

            <label class="block text-sm sm:col-span-2">
                <span class="mb-1 block text-slate-700">{{ __('Default receipt footer') }}</span>
                <textarea name="default_receipt_footer" rows="2" class="w-full rounded-md border border-slate-300 px-2 py-1.5">{{ old('default_receipt_footer', $values['default_receipt_footer']) }}</textarea>
            </label>

            <label class="block text-sm sm:col-span-2">
                <span class="mb-1 block text-slate-700">{{ __('Default report-card footer') }}</span>
                <textarea name="default_report_card_footer" rows="2" class="w-full rounded-md border border-slate-300 px-2 py-1.5">{{ old('default_report_card_footer', $values['default_report_card_footer']) }}</textarea>
            </label>

            <label class="inline-flex items-center gap-2 text-sm sm:col-span-2">
                <input type="checkbox" name="parent_can_view_discipline" value="1" @checked(! empty($values['parent_can_view_discipline'])) class="rounded border-slate-300" />
                <span>{{ __('Allow parents to view discipline incidents for their child') }}</span>
            </label>
        </div>

        <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm text-white hover:bg-primary/95">{{ __('Save settings') }}</button>
    </form>
@endsection
