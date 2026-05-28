@extends('layouts.app')

@section('title', __('School settings'))

@section('header-title', __('Settings'))

@section('content')
    @include('settings._subnav')

    <h1 class="text-2xl font-semibold text-primary">{{ __('School profile') }}</h1>
    <p class="mt-1 text-sm text-gray-600">{{ __('Official name, contact, and academic year.') }}</p>

    <div class="mt-8 max-w-2xl rounded-lg border border-gray-200 bg-page p-6">
        <form method="POST" action="{{ route('settings.school.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('School name') }} <span class="text-red-600">*</span></label>
                <input id="name" name="name" type="text" required value="{{ old('name', $school->name) }}"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="logo" class="block text-sm font-medium text-gray-700">{{ __('Logo') }}</label>
                @if ($school->logo)
                    <p class="mt-1 text-xs text-gray-500">{{ __('Current file on disk; upload a new image to replace.') }}</p>
                    <img src="{{ Storage::disk('public')->url($school->logo) }}" alt="" class="mt-2 h-16 w-auto rounded border border-gray-200 object-contain">
                @endif
                <input id="logo" name="logo" type="file" accept="image/*"
                    class="mt-2 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-primary/95 @error('logo') border border-red-500 rounded-md @enderror">
                @error('logo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">{{ __('Address') }}</label>
                <textarea id="address" name="address" rows="3"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('address') border-red-500 @enderror">{{ old('address', $school->address) }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('Phone') }}</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $school->phone) }}"
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $school->email) }}"
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="academic_year" class="block text-sm font-medium text-gray-700">{{ __('Academic year') }}</label>
                <input id="academic_year" name="academic_year" type="text" value="{{ old('academic_year', $school->academic_year) }}" placeholder="{{ __('e.g. 2025/2026') }}"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('academic_year') border-red-500 @enderror">
                @error('academic_year')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">{{ __('This is just a display label; manage real academic years under Settings → Academic years.') }}</p>
            </div>

            <div>
                <label for="motto" class="block text-sm font-medium text-gray-700">{{ __('School motto') }}</label>
                <input id="motto" name="motto" type="text" value="{{ old('motto', $school->motto) }}"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('motto') border-red-500 @enderror">
                @error('motto')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="head_teacher_name" class="block text-sm font-medium text-gray-700">{{ __('Headteacher name') }}</label>
                <input id="head_teacher_name" name="head_teacher_name" type="text" value="{{ old('head_teacher_name', $school->head_teacher_name) }}"
                    class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('head_teacher_name') border-red-500 @enderror">
                @error('head_teacher_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="ges_region" class="block text-sm font-medium text-gray-700">{{ __('GES region') }}</label>
                    <input id="ges_region" name="ges_region" type="text" value="{{ old('ges_region', $school->ges_region) }}"
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('ges_region') border-red-500 @enderror">
                    @error('ges_region')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="ges_district" class="block text-sm font-medium text-gray-700">{{ __('GES district') }}</label>
                    <input id="ges_district" name="ges_district" type="text" value="{{ old('ges_district', $school->ges_district) }}"
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('ges_district') border-red-500 @enderror">
                    @error('ges_district')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="ges_circuit" class="block text-sm font-medium text-gray-700">{{ __('GES circuit') }}</label>
                    <input id="ges_circuit" name="ges_circuit" type="text" value="{{ old('ges_circuit', $school->ges_circuit) }}"
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('ges_circuit') border-red-500 @enderror">
                    @error('ges_circuit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="school_code" class="block text-sm font-medium text-gray-700">{{ __('School code') }}</label>
                    <input id="school_code" name="school_code" type="text" value="{{ old('school_code', $school->school_code) }}"
                        class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('school_code') border-red-500 @enderror">
                    @error('school_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/95">{{ __('Save changes') }}</button>
            </div>
        </form>
    </div>
@endsection
