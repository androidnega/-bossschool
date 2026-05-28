<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlatformAppSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allow_school_registration' => $this->boolean('allow_school_registration'),
            'require_subscription_payment' => $this->boolean('require_subscription_payment'),
            'maintenance_enabled' => $this->boolean('maintenance_enabled'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'platform_name' => ['required', 'string', 'max:128'],
            'support_email' => ['required', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:64'],
            'default_trial_days' => ['required', 'integer', 'min:1', 'max:365'],
            'allow_school_registration' => ['sometimes', 'boolean'],
            'require_subscription_payment' => ['sometimes', 'boolean'],
            'default_currency' => ['required', 'string', 'max:8'],
            'maintenance_enabled' => ['sometimes', 'boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
