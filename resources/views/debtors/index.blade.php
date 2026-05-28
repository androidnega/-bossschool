@extends('layouts.app')

@section('title', __('Debtors'))

@section('header-title', 'Fees')

@section('content')
    @include('finances._subnav')

    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-primary">{{ __('Debtors') }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ __('Expected fees (by class), paid totals, and balance.') }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 bg-page p-4">
        <form method="GET" action="{{ route('debtors.index') }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="w-full sm:w-56">
                <label for="class_id" class="block text-sm font-medium text-gray-700">{{ __('Class') }}</label>
                <select id="class_id" name="class_id"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="">{{ __('All classes') }}</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected($filters['class_id'] === (string) $class->id)>
                            {{ $class->name }}@if($class->section) — {{ $class->section }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Apply') }}</button>
                <a href="{{ route('debtors.index') }}" class="rounded-md border border-gray-300 bg-page px-4 py-2 text-sm font-medium text-gray-700 hover:bg-page-soft">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-page">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-page-soft">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">{{ __('Student') }}</th>
                        <th class="hidden px-4 py-3 font-medium text-gray-700 sm:table-cell">{{ __('Class') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Expected') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Paid') }}</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Balance') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-page">
                    @forelse ($students as $student)
                        @php
                            $expected = (float) ($expectedByClass[(int) $student->class_id] ?? 0);
                            $paid = (float) ($paidByStudent[$student->id] ?? 0);
                            $balance = $expected - $paid;
                        @endphp
                        <tr class="hover:bg-page-soft/80">
                            <td class="px-4 py-3">
                                <a href="{{ route('students.show', $student) }}" class="font-medium text-primary hover:underline">{{ $student->name }}</a>
                                <div class="mt-0.5 text-xs text-gray-500 sm:hidden">{{ $student->schoolClass?->name }}@if($student->schoolClass?->section) — {{ $student->schoolClass->section }}@endif</div>
                            </td>
                            <td class="hidden px-4 py-3 text-gray-700 sm:table-cell">{{ $student->schoolClass?->name }}@if($student->schoolClass?->section) — {{ $student->schoolClass->section }}@endif</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ cedis($expected) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ cedis($paid) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium {{ $balance > 0 ? 'text-amber-900/90' : 'text-gray-900' }}">{{ cedis($balance) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-600">{{ __('No students found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($students->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $students->links() }}</div>
        @endif
    </div>
@endsection
