@php
    /** @var \App\Models\Result|null $result */
    $result = $result ?? null;
    $num = fn (string $field) => old($field, $result !== null && $result->getAttribute($field) !== null ? (string) $result->getAttribute($field) : '');
    $canOverrideTerm = $canOverrideTerm ?? false;
    $currentYearId = old('academic_year_id', $result?->academic_year_id ?? ($currentYear?->id ?? null));
    $currentTermId = old('term_id', $result?->term_id ?? ($currentTerm?->id ?? null));
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    @if ($canOverrideTerm)
        <div>
            <label for="academic_year_id" class="block text-sm font-medium text-gray-700">{{ __('Academic year') }}</label>
            <select id="academic_year_id" name="academic_year_id"
                class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('academic_year_id') border-red-500 @enderror">
                @foreach ($years as $year)
                    <option value="{{ $year->id }}" @selected((string) $currentYearId === (string) $year->id)>{{ $year->name }}@if($year->is_current) ({{ __('current') }})@endif</option>
                @endforeach
            </select>
            @error('academic_year_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="term_id" class="block text-sm font-medium text-gray-700">{{ __('Term') }}</label>
            <select id="term_id" name="term_id"
                class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('term_id') border-red-500 @enderror">
                @foreach ($terms as $term)
                    <option value="{{ $term->id }}" @selected((string) $currentTermId === (string) $term->id)>{{ $term->name }} · {{ $term->academicYear?->name }}@if($term->is_current) ({{ __('current') }})@endif</option>
                @endforeach
            </select>
            @error('term_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    @else
        <div class="sm:col-span-2 rounded-lg border border-emerald-200 bg-emerald-50/60 px-3 py-2 text-sm text-emerald-900">
            <strong>{{ __('Recording for') }}:</strong>
            {{ $currentYear?->name ?? __('No current academic year set') }} · {{ $currentTerm?->name ?? __('No current term set') }}
        </div>
    @endif

    <div class="sm:col-span-2">
        <label for="student_id" class="block text-sm font-medium text-gray-700">{{ __('Student') }} <span class="text-red-600">*</span></label>
        <select id="student_id" name="student_id" required
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('student_id') border-red-500 @enderror">
            <option value="">—</option>
            @foreach ($students as $stu)
                <option value="{{ $stu->id }}" @selected((string) old('student_id', $result?->student_id) === (string) $stu->id)>
                    {{ $stu->name }}
                    @if($stu->schoolClass)
                        ({{ $stu->schoolClass->name }}@if($stu->schoolClass->section) — {{ $stu->schoolClass->section }}@endif)
                    @endif
                </option>
            @endforeach
        </select>
        @error('student_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="subject_id" class="block text-sm font-medium text-gray-700">{{ __('Subject') }} <span class="text-red-600">*</span></label>
        <select id="subject_id" name="subject_id" required
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('subject_id') border-red-500 @enderror">
            <option value="">—</option>
            @foreach ($subjects as $sub)
                <option value="{{ $sub->id }}" @selected((string) old('subject_id', $result?->subject_id) === (string) $sub->id)>
                    {{ $sub->name }}
                    @if($sub->schoolClass)
                        ({{ $sub->schoolClass->name }}@if($sub->schoolClass->section) — {{ $sub->schoolClass->section }}@endif)
                    @endif
                </option>
            @endforeach
        </select>
        @error('subject_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="class_test" class="block text-sm font-medium text-gray-700">{{ __('Class test') }}</label>
        <input id="class_test" name="class_test" type="number" step="0.01" min="0" value="{{ $num('class_test') }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('class_test') border-red-500 @enderror">
        @error('class_test')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="midterm" class="block text-sm font-medium text-gray-700">{{ __('Midterm') }}</label>
        <input id="midterm" name="midterm" type="number" step="0.01" min="0" value="{{ $num('midterm') }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('midterm') border-red-500 @enderror">
        @error('midterm')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="exam" class="block text-sm font-medium text-gray-700">{{ __('Exam') }}</label>
        <input id="exam" name="exam" type="number" step="0.01" min="0" value="{{ $num('exam') }}"
            class="mt-1 w-full rounded-md border border-gray-300 bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary @error('exam') border-red-500 @enderror">
        @error('exam')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

@if($result)
    <p class="mt-4 text-sm text-gray-600">{{ __('Total and grade are recalculated when you save (A: 80–100, B: 70–79, C: 60–69, D: 50–59, F: 0–49).') }}</p>
    <div class="mt-2 flex flex-wrap gap-4 text-sm">
        <span class="text-gray-700">{{ __('Current total') }}: <strong class="tabular-nums text-gray-900">{{ number_format((float) $result->total, 2) }}</strong></span>
        <span class="text-gray-700">{{ __('Current grade') }}: <strong class="text-primary">{{ $result->grade }}</strong></span>
    </div>
@else
    <p class="mt-4 text-sm text-gray-600">{{ __('Total and letter grade are assigned automatically on save.') }}</p>
@endif
