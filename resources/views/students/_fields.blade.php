@php
    /** @var \App\Models\Student|null $student */
    $student = $student ?? null;
    $val = fn (string $field, mixed $default = null) => old($field, $student !== null ? $student->getAttribute($field) : $default);
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-600">*</span></label>
        <input id="name" name="name" type="text" required value="{{ $val('name', '') }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('name') border-red-500 @enderror">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
        <select id="gender" name="gender"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('gender') border-red-500 @enderror">
            <option value="">—</option>
            <option value="male" @selected($val('gender') === 'male')>Male</option>
            <option value="female" @selected($val('gender') === 'female')>Female</option>
            <option value="other" @selected($val('gender') === 'other')>Other</option>
        </select>
        @error('gender')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="dob" class="block text-sm font-medium text-gray-700">Date of birth</label>
        <input id="dob" name="dob" type="date" value="{{ old('dob', $student?->dob?->format('Y-m-d')) }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('dob') border-red-500 @enderror">
        @error('dob')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="class_id" class="block text-sm font-medium text-gray-700">Class <span class="text-red-600">*</span></label>
        <select id="class_id" name="class_id" required
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('class_id') border-red-500 @enderror">
            <option value="">Select class</option>
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

    <div>
        <label for="parent_name" class="block text-sm font-medium text-gray-700">Parent / guardian name</label>
        <input id="parent_name" name="parent_name" type="text" value="{{ $val('parent_name', '') }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('parent_name') border-red-500 @enderror">
        @error('parent_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="parent_phone" class="block text-sm font-medium text-gray-700">Parent phone</label>
        <input id="parent_phone" name="parent_phone" type="text" value="{{ $val('parent_phone', '') }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('parent_phone') border-red-500 @enderror">
        @error('parent_phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
        <textarea id="address" name="address" rows="3"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('address') border-red-500 @enderror">{{ $val('address', '') }}</textarea>
        @error('address')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="admission_date" class="block text-sm font-medium text-gray-700">Admission date</label>
        <input id="admission_date" name="admission_date" type="date" value="{{ old('admission_date', $student?->admission_date?->format('Y-m-d')) }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('admission_date') border-red-500 @enderror">
        @error('admission_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">Status <span class="text-red-600">*</span></label>
        <select id="status" name="status" required
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('status') border-red-500 @enderror">
            @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'graduated' => 'Graduated', 'transferred' => 'Transferred'] as $value => $label)
                <option value="{{ $value }}" @selected($val('status', 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
