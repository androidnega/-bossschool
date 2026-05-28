@extends('layouts.app')

@section('title', __('Mark staff attendance'))
@section('header-title', __('Mark staff attendance'))

@section('content')
    <form method="POST" action="{{ route('staff-attendance.store') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        <div class="flex flex-wrap items-end gap-3">
            <label class="block text-sm">
                <span class="mb-1 block text-slate-700">{{ __('Date') }}</span>
                <input type="date" name="date" value="{{ $date }}" class="rounded-md border border-slate-300 px-2 py-1.5" required />
            </label>
            @if($currentYear)
                <input type="hidden" name="academic_year_id" value="{{ $currentYear->id }}" />
            @endif
            @if($currentTerm)
                <input type="hidden" name="term_id" value="{{ $currentTerm->id }}" />
            @endif
        </div>

        <div class="overflow-x-auto rounded-md border border-slate-200">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2">{{ __('Staff') }}</th>
                        <th class="px-3 py-2">{{ __('Status') }}</th>
                        <th class="px-3 py-2">{{ __('Remarks') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($staff as $person)
                        @php($current = $existing->get($person->id))
                        <tr>
                            <td class="px-3 py-2">{{ $person->name }} <span class="text-xs text-slate-500">({{ $person->role }})</span></td>
                            <td class="px-3 py-2">
                                <select name="statuses[{{ $person->id }}]" class="rounded-md border border-slate-300 px-2 py-1.5">
                                    @foreach(\App\Models\StaffAttendance::STATUSES as $s)
                                        <option value="{{ $s }}" @selected(($current?->status ?? \App\Models\StaffAttendance::STATUS_PRESENT) === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="remarks[{{ $person->id }}]" value="{{ $current?->remarks }}" maxlength="255" class="w-full rounded-md border border-slate-300 px-2 py-1.5" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm text-white hover:bg-primary/95">{{ __('Save staff attendance') }}</button>
    </form>
@endsection
