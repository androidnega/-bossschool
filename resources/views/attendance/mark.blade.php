@extends('layouts.app')

@section('title', __('Mark attendance'))

@section('header-title', __('Attendance'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('attendance.index') }}" class="text-sm font-medium text-secondary hover:text-primary">← {{ __('Back to attendance') }}</a>
        <h1 class="mt-2 text-2xl font-semibold text-primary">{{ __('Mark attendance') }} — {{ $class->name }}@if($class->section) ({{ $class->section }})@endif</h1>
    </div>

    @if (! $currentTerm || ! $currentYear)
        <div class="rounded-lg border border-amber-200 bg-amber-50/60 px-4 py-3 text-sm text-amber-900">
            {{ __('You must set a current academic year and term before marking attendance.') }}
        </div>
    @else
        <div class="rounded-lg border border-emerald-200 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">
            <strong>{{ $currentYear->name }} · {{ $currentTerm->name }}</strong> — {{ __('Tick the status for each student then save.') }}
        </div>

        @if (session('status'))
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('attendance.store', ['schoolClass' => $class]) }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $currentYear->id }}">
            <input type="hidden" name="term_id" value="{{ $currentTerm->id }}">

            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">{{ __('Date') }}</label>
                    <input id="date" name="date" type="date" required value="{{ $date }}" class="mt-1 rounded-md border border-gray-300 bg-page px-3 py-2 text-sm">
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-page">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-page-soft">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Student') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Status') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Remarks') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-page">
                        @forelse ($students as $student)
                            @php $current = $existing->get($student->id); @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $student->name }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-4 text-sm">
                                        @foreach (\App\Models\Attendance::STATUSES as $status)
                                            <label class="inline-flex items-center gap-1.5">
                                                <input type="radio" name="statuses[{{ $student->id }}]" value="{{ $status }}" @checked(($current?->status ?? 'present') === $status) class="h-4 w-4 border-gray-300 text-primary focus:ring-primary">
                                                <span class="capitalize">{{ $status }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="remarks[{{ $student->id }}]" value="{{ $current?->remarks }}" maxlength="255" class="w-full rounded-md border border-gray-300 bg-page px-2 py-1.5 text-sm">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-600">{{ __('No active students in this class.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Save attendance') }}</button>
                <a href="{{ route('attendance.index') }}" class="rounded-md border border-gray-300 bg-page px-4 py-2 text-sm font-medium text-gray-700 hover:bg-page-soft">{{ __('Cancel') }}</a>
            </div>
        </form>
    @endif
@endsection
