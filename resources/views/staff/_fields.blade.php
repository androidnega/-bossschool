@php
    /** @var \App\Models\Staff|null $staff */
    $staff = $staff ?? null;
@endphp
<div class="space-y-4">
    <div>
        <label for="staff_name" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Name') }} <span class="text-rose-600">*</span></label>
        <input id="staff_name" name="name" type="text" required maxlength="255" value="{{ old('name', $staff?->name) }}"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm @error('name') border-rose-500 @enderror" />
        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="staff_role" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Job title / role') }}</label>
        <input id="staff_role" name="role" type="text" maxlength="128" value="{{ old('role', $staff?->role) }}"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm @error('role') border-rose-500 @enderror"
            placeholder="{{ __('e.g. Teacher, Bursar') }}" />
        @error('role')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="staff_subject" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Subject / specialty') }}</label>
        <input id="staff_subject" name="subject" type="text" maxlength="128" value="{{ old('subject', $staff?->subject) }}"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm @error('subject') border-rose-500 @enderror"
            placeholder="{{ __('Optional — main subject taught') }}" />
        @error('subject')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="staff_phone" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Phone') }}</label>
        <input id="staff_phone" name="phone" type="text" maxlength="64" value="{{ old('phone', $staff?->phone) }}"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm @error('phone') border-rose-500 @enderror" />
        @error('phone')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="staff_salary" class="mb-1 block text-sm font-medium text-slate-700">{{ __('Salary') }}</label>
        <input id="staff_salary" name="salary" type="number" step="0.01" min="0" value="{{ old('salary', $staff?->salary) }}"
            class="mt-1 w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2 text-sm @error('salary') border-rose-500 @enderror"
            placeholder="{{ __('Optional') }}" />
        @error('salary')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>
