@php
    /** @var \App\Models\Fee|null $fee */
    $fee = $fee ?? null;
    $val = fn (string $field, mixed $default = null) => old($field, $fee !== null ? $fee->getAttribute($field) : $default);
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="class_id" class="block text-sm font-medium text-gray-700">Class <span class="text-red-600">*</span></label>
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

    <div>
        <label for="term_id" class="block text-sm font-medium text-gray-700">Term <span class="text-red-600">*</span></label>
        <select id="term_id" name="term_id" required
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('term_id') border-red-500 @enderror">
            <option value="">—</option>
            @foreach ($terms as $term)
                <option value="{{ $term->id }}" @selected((string) $val('term_id') === (string) $term->id)>{{ $term->name }}</option>
            @endforeach
        </select>
        @error('term_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="fee_type" class="block text-sm font-medium text-gray-700">Fee type <span class="text-red-600">*</span></label>
        <input id="fee_type" name="fee_type" type="text" required maxlength="128" value="{{ $val('fee_type', '') }}" placeholder="{{ __('e.g. Tuition, PTA, Sports') }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('fee_type') border-red-500 @enderror">
        @error('fee_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="amount" class="block text-sm font-medium text-gray-700">{{ __('Amount (GH₵)') }} <span class="text-red-600">*</span></label>
        <input id="amount" name="amount" type="number" step="0.01" min="0" required value="{{ $val('amount', '') }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('amount') border-red-500 @enderror">
        @error('amount')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
