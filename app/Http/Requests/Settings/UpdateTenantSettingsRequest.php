<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('settings.manage');
    }

    public function rules(): array
    {
        return [
            'default_pass_mark' => ['nullable', 'integer', 'min:0', 'max:100'],
            'default_sms_provider' => ['nullable', 'string', 'in:log,hubtel,mnotify,arkesel,twilio'],
            'parent_online_payment_enabled' => ['nullable', 'boolean'],
            'student_online_payment_enabled' => ['nullable', 'boolean'],
            'default_report_card_grading_mode' => ['nullable', Rule::in(['WAEC', 'Cambridge', 'Custom'])],
            'sender_id' => ['nullable', 'string', 'max:11'],
            'default_invoice_due_days' => ['nullable', 'integer', 'min:0', 'max:120'],
            'default_receipt_footer' => ['nullable', 'string', 'max:500'],
            'default_report_card_footer' => ['nullable', 'string', 'max:500'],
            'default_attendance_days_per_term' => ['nullable', 'integer', 'min:1', 'max:200'],
            'parent_can_view_discipline' => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        // HTML checkboxes only POST when ticked; coerce nulls to false so the
        // saved settings always have a deterministic shape.
        foreach (['parent_online_payment_enabled', 'student_online_payment_enabled', 'parent_can_view_discipline'] as $bool) {
            $this->merge([$bool => (bool) $this->input($bool, false)]);
        }
    }
}
