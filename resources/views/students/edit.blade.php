@extends('layouts.app')

@section('title', 'Edit '.$student->name)

@section('header-title', 'Students')

@section('content')
    <div class="mb-6">
        <a href="{{ route('students.show', $student) }}" class="text-sm font-medium text-secondary hover:text-primary">← Back to profile</a>
        <h1 class="mt-2 text-2xl font-semibold text-primary">Edit student</h1>
    </div>

    <div class="max-w-3xl rounded-lg border border-gray-200 bg-page p-6">
        <form method="POST" action="{{ route('students.update', $student) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('students._fields', ['classes' => $classes, 'student' => $student])

            <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-6">
                <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">Save changes</button>
                <a href="{{ route('students.show', $student) }}" class="rounded-md border border-gray-300 bg-page px-4 py-2 text-sm font-medium text-gray-700 hover:bg-page-soft">Cancel</a>
            </div>
        </form>
    </div>
@endsection
