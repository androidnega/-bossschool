<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeatureTogglesRequest extends FormRequest
{
    public const GLOBAL_KEYS = [
        'parent_portal',
        'student_portal',
        'online_payments',
        'attendance',
        'messaging',
        'report_cards',
        'tenant_registration',
        'maintenance_banner',
    ];

    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $merged = [];
        foreach (self::GLOBAL_KEYS as $key) {
            $merged[$key] = $this->boolean('toggles.'.$key);
        }
        $this->merge(['toggles' => $merged]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'toggles' => ['required', 'array'],
        ];
        foreach (self::GLOBAL_KEYS as $key) {
            $rules['toggles.'.$key] = ['boolean'];
        }

        return $rules;
    }
}
