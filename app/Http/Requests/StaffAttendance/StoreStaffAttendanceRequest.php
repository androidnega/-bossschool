<?php

namespace App\Http\Requests\StaffAttendance;

use App\Models\StaffAttendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', StaffAttendance::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'term_id' => ['nullable', 'integer', 'exists:terms,id'],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*' => ['required', Rule::in(StaffAttendance::STATUSES)],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
