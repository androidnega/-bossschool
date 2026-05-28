@extends('layouts.app')

@section('title', __('Students'))

@section('header-title', 'Students')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-primary">Students</h1>
            <p class="mt-1 text-sm text-gray-600">Search and filter by class.</p>
        </div>
        @can('create', \App\Models\Student::class)
            <a href="{{ route('students.create') }}" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">
                Add student
            </a>
        @endcan
    </div>

    <div class="mt-6 rounded-lg border border-gray-200 bg-page p-4">
        <form method="GET" action="{{ route('students.index') }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="q" class="block text-sm font-medium text-gray-700">Search</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Name, parent, phone"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
            <div class="w-full sm:w-48">
                <label for="class_id" class="block text-sm font-medium text-gray-700">Class</label>
                <select id="class_id" name="class_id"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="">All classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected($filters['class_id'] === (string) $class->id)>
                            {{ $class->name }}@if($class->section) — {{ $class->section }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">Apply</button>
                <a href="{{ route('students.index') }}" class="rounded-md border border-gray-300 bg-page px-4 py-2 text-sm font-medium text-gray-700 hover:bg-page-soft">Reset</a>
            </div>
        </form>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-page">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-page-soft">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">Name</th>
                        <th class="hidden px-4 py-3 font-medium text-gray-700 sm:table-cell">Class</th>
                        <th class="hidden px-4 py-3 font-medium text-gray-700 md:table-cell">Status</th>
                        <th class="px-4 py-3 font-medium text-gray-700 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-page">
                    @forelse ($students as $student)
                        <tr class="hover:bg-page-soft/80">
                            <td class="px-4 py-3">
                                <a href="{{ route('students.show', $student) }}" class="font-medium text-primary hover:underline">{{ $student->name }}</a>
                                <div class="mt-0.5 text-xs text-gray-500 sm:hidden">{{ $student->schoolClass?->name }}@if($student->schoolClass?->section) — {{ $student->schoolClass->section }}@endif · {{ ucfirst($student->status) }}</div>
                            </td>
                            <td class="hidden px-4 py-3 text-gray-700 sm:table-cell">
                                {{ $student->schoolClass?->name }}@if($student->schoolClass?->section) — {{ $student->schoolClass->section }}@endif
                            </td>
                            <td class="hidden px-4 py-3 text-gray-700 md:table-cell">{{ ucfirst($student->status) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('students.show', $student) }}" class="text-primary hover:underline">View</a>
                                @can('update', $student)
                                    <span class="text-gray-300">·</span>
                                    <a href="{{ route('students.edit', $student) }}" class="text-primary hover:underline">Edit</a>
                                @endcan
                                @can('delete', $student)
                                    <span class="text-gray-300">·</span>
                                    <form action="{{ route('students.destroy', $student) }}" method="POST" class="inline" onsubmit="return confirm({{ json_encode(__('Remove this student?')) }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-0">
                                <div class="p-6">
                                    <x-empty-state :title="__('No students found')" :message="__('Try adjusting search or class filters, or add a new student.')" />
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($students->hasPages())
            <div class="border-t border-gray-200 bg-page px-4 py-3">
                {{ $students->links() }}
            </div>
        @endif
    </div>
@endsection
