@extends('layouts.app')

@section('title', $student->name)

@section('header-title', 'Students')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('students.index') }}" class="text-sm font-medium text-secondary hover:text-primary">← Back to students</a>
            <h1 class="mt-2 text-2xl font-semibold text-primary">{{ $student->name }}</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ $student->schoolClass?->name }}@if($student->schoolClass?->section) — {{ $student->schoolClass->section }}@endif
                <span class="text-gray-400">·</span>
                <span class="capitalize">{{ $student->status }}</span>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('viewAny', \App\Models\Result::class)
                @can('view', $student)
                    <a href="{{ route('students.report-card', $student) }}" class="rounded-md border border-secondary/60 bg-page px-4 py-2 text-sm font-medium text-secondary hover:bg-page-soft">{{ __('Report card') }}</a>
                @endcan
            @endcan
            @can('update', $student)
                <a href="{{ route('students.edit', $student) }}" class="rounded-md border border-secondary/60 bg-page px-4 py-2 text-sm font-medium text-secondary hover:bg-page-soft">Edit</a>
            @endcan
            @can('delete', $student)
                <form action="{{ route('students.destroy', $student) }}" method="POST" onsubmit="return confirm({{ json_encode(__('Remove this student?')) }})">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-md border border-red-200 bg-page px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-page p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-secondary">Student</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Gender</dt>
                    <dd class="mt-0.5 text-gray-900 capitalize">{{ $student->gender ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Date of birth</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $student->dob?->format('M j, Y') ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Admission date</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $student->admission_date?->format('M j, Y') ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border border-gray-200 bg-page p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-secondary">Contact</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Parent / guardian</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $student->parent_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Parent phone</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $student->parent_phone ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Address</dt>
                    <dd class="mt-0.5 whitespace-pre-wrap text-gray-900">{{ $student->address ?: '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
