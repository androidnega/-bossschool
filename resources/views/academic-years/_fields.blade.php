@php
    /** @var \App\Models\AcademicYear|null $year */
    $year = $year ?? null;
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name (e.g. 2025/2026)') }} <span class="text-red-600">*</span></label>
        <input id="name" name="name" type="text" required value="{{ old('name', $year?->name) }}" maxlength="32"
            placeholder="2025/2026"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('name') border-red-500 @enderror">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="starts_on" class="block text-sm font-medium text-gray-700">{{ __('Starts on') }} <span class="text-red-600">*</span></label>
        <input id="starts_on" name="starts_on" type="date" required value="{{ old('starts_on', $year?->starts_on?->toDateString()) }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('starts_on') border-red-500 @enderror">
        @error('starts_on')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="ends_on" class="block text-sm font-medium text-gray-700">{{ __('Ends on') }} <span class="text-red-600">*</span></label>
        <input id="ends_on" name="ends_on" type="date" required value="{{ old('ends_on', $year?->ends_on?->toDateString()) }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('ends_on') border-red-500 @enderror">
        @error('ends_on')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
        <select id="status" name="status"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            @php $statusValue = old('status', $year?->status ?? \App\Models\AcademicYear::STATUS_ACTIVE); @endphp
            <option value="{{ \App\Models\AcademicYear::STATUS_ACTIVE }}" @selected($statusValue === \App\Models\AcademicYear::STATUS_ACTIVE)>{{ __('Active') }}</option>
            <option value="{{ \App\Models\AcademicYear::STATUS_ARCHIVED }}" @selected($statusValue === \App\Models\AcademicYear::STATUS_ARCHIVED)>{{ __('Archived') }}</option>
        </select>
    </div>

    <div class="sm:col-span-2 flex items-center gap-2">
        <input id="is_current" name="is_current" type="checkbox" value="1" @checked(old('is_current', $year?->is_current ?? false)) class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
        <label for="is_current" class="text-sm text-gray-700">{{ __('Mark as current academic year') }}</label>
    </div>
</div>
