@extends('layouts.app')

@section('title', __('Classes'))

@section('header-title', ($readOnly ?? false) ? __('My classes') : __('Settings'))

@section('content')
    @unless($readOnly ?? false)
        @include('settings._subnav')
    @endunless

    <h1 class="text-2xl font-semibold text-primary">{{ ($readOnly ?? false) ? __('My classes') : __('Classes') }}</h1>
    <p class="mt-1 text-sm text-gray-600">{{ ($readOnly ?? false) ? __('Classes you are assigned to teach.') : __('Manage class names and sections.') }}</p>

    @unless($readOnly ?? false)
    <datalist id="class-name-suggestions">
        @foreach (\App\Models\SchoolClass::GHANA_SUGGESTIONS as $suggestion)
            <option value="{{ $suggestion }}"></option>
        @endforeach
    </datalist>

    <div class="mt-8 rounded-lg border border-gray-200 bg-page p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-secondary">{{ __('Add class') }}</h2>
        <p class="mt-1 text-xs text-gray-500">{{ __('Start typing to see Ghanaian suggestions (KG, Basic, JHS). Custom names are still allowed.') }}</p>
        <form method="POST" action="{{ route('classes.store') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            @csrf
            <div class="min-w-0 flex-1 sm:max-w-xs">
                <label for="new_name" class="block text-sm font-medium text-gray-700">{{ __('Name') }} <span class="text-red-600">*</span></label>
                <input id="new_name" name="name" type="text" required value="{{ old('name') }}" list="class-name-suggestions" autocomplete="off"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('name') border-red-500 @enderror">
            </div>
            <div class="w-full sm:w-40">
                <label for="new_section" class="block text-sm font-medium text-gray-700">{{ __('Section') }}</label>
                <input id="new_section" name="section" type="text" value="{{ old('section') }}"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
            <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Add') }}</button>
        </form>
        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    @endunless

    <div class="mt-8 overflow-hidden rounded-lg border border-gray-200 bg-page">
        @if ($classes->isEmpty())
            <div class="p-6">
                <x-empty-state :title="__('No classes yet')" :message="__('Add a class above to use it for students and fees.')" />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-page-soft">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Name') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700">{{ __('Section') }}</th>
                            <th class="px-4 py-3 font-medium text-gray-700 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-page">
                        @foreach ($classes as $class)
                            <tr class="hover:bg-page-soft/80">
                                @if ($readOnly ?? false)
                                    <td class="px-4 py-3 text-gray-900">{{ $class->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $class->section ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-400">—</td>
                                @else
                                <td class="px-4 py-3 align-top" colspan="3">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                                        <form method="POST" action="{{ route('classes.update', $class) }}" class="flex flex-1 flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                                            @csrf
                                            @method('PUT')
                                            <div class="min-w-0 flex-1 sm:max-w-xs">
                                                <label class="mb-1 block text-xs font-medium text-gray-500 sm:sr-only" for="name_{{ $class->id }}">{{ __('Name') }}</label>
                                                <input id="name_{{ $class->id }}" name="name" type="text" required value="{{ $class->name }}" list="class-name-suggestions" autocomplete="off"
                                                    class="w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                            </div>
                                            <div class="w-full sm:w-36">
                                                <label class="mb-1 block text-xs font-medium text-gray-500 sm:sr-only" for="section_{{ $class->id }}">{{ __('Section') }}</label>
                                                <input id="section_{{ $class->id }}" name="section" type="text" value="{{ $class->section }}"
                                                    class="w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                            </div>
                                            <button type="submit" class="rounded-md border border-secondary/60 bg-page px-4 py-2 text-sm font-medium text-secondary hover:bg-page-soft">{{ __('Save') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('classes.destroy', $class) }}" class="sm:ml-auto" onsubmit="return confirm({{ json_encode(__('Delete this class?')) }})">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md px-3 py-2 text-sm text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
