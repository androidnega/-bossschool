@php
    /** @var \App\Models\Subject|null $subject */
    $subject = $subject ?? null;
    $val = fn (string $field, mixed $default = null) => old($field, $subject !== null ? $subject->getAttribute($field) : $default);
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="class_id" class="block text-sm font-medium text-gray-700">{{ __('Class') }} <span class="text-red-600">*</span></label>
        <select id="class_id" name="class_id" required
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('class_id') border-red-500 @enderror">
            <option value="">—</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected((string) $val('class_id') === (string) $class->id)>
                    {{ $class->name }}@if($class->section) — {{ $class->section }}@endif
                </option>
            @endforeach
        </select>
        @error('class_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Subject name') }} <span class="text-red-600">*</span></label>
        <input id="name" name="name" type="text" required maxlength="255" value="{{ $val('name', '') }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('name') border-red-500 @enderror">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
