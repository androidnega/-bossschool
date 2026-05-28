<?php

namespace App\Http\Requests\Attendance;

use App\Models\Attendance;
use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SchoolClass $class */
        $class = $this->route('schoolClass');

        return $this->user()->can('markForClass', [Attendance::class, $class]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $tenantId = (int) $this->user()->tenant_id;

        return [
            'date' => ['required', 'date'],
            'term_id' => [
                'required',
                Rule::exists('terms', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', Rule::in(Attendance::STATUSES)],
            'remarks' => ['array'],
            'remarks.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
