@extends('layouts.app')

@section('title', __('Teacher assignments'))
@section('header-title', __('Teacher subject & class assignments'))

@section('content')
    @if(session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <aside class="lg:col-span-1">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold text-slate-700">{{ __('Teachers') }}</h2>
                <ul class="divide-y divide-slate-100 text-sm">
                    @forelse($teachers as $t)
                        <li class="py-1.5">
                            <a href="{{ route('staff.assignments.index', ['teacher_id' => $t->id]) }}"
                               class="block rounded px-2 py-1 hover:bg-slate-50 {{ $teacher && $t->id === $teacher->id ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-700' }}">
                                {{ $t->name }}
                            </a>
                        </li>
                    @empty
                        <li class="py-2 text-slate-500">{{ __('No teachers yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        </aside>

        <section class="lg:col-span-2">
            @if($teacher)
                <form method="POST" action="{{ route('staff.assignments.update', $teacher) }}" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6">
                    @csrf
                    @method('PUT')
                    <h2 class="text-lg font-semibold text-slate-900">{{ $teacher->name }}</h2>

                    <fieldset>
                        <legend class="mb-2 text-sm font-semibold text-slate-700">{{ __('Subjects') }}</legend>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3">
                            @forelse($subjects as $subject)
                                <label class="inline-flex items-center gap-2 rounded border border-slate-200 px-2 py-1 text-sm">
                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}"
                                           @checked($assignedSubjectIds->contains($subject->id))
                                           class="rounded border-slate-300" />
                                    {{ $subject->name }}
                                </label>
                            @empty
                                <p class="text-sm text-slate-500">{{ __('No subjects yet.') }}</p>
                            @endforelse
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="mb-2 text-sm font-semibold text-slate-700">{{ __('Classes') }}</legend>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3">
                            @forelse($classes as $class)
                                <label class="inline-flex items-center gap-2 rounded border border-slate-200 px-2 py-1 text-sm">
                                    <input type="checkbox" name="class_ids[]" value="{{ $class->id }}"
                                           @checked($assignedClassIds->contains($class->id))
                                           class="rounded border-slate-300" />
                                    {{ $class->name }} {{ $class->section ? '· '.$class->section : '' }}
                                </label>
                            @empty
                                <p class="text-sm text-slate-500">{{ __('No classes yet.') }}</p>
                            @endforelse
                        </div>
                    </fieldset>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm text-white hover:bg-primary/95">{{ __('Save assignments') }}</button>
                        <a href="{{ route('staff.assignments.index') }}" class="text-sm text-slate-500 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            @else
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-slate-500">
                    {{ __('Choose a teacher on the left to manage assignments.') }}
                </div>
            @endif
        </section>
    </div>
@endsection
