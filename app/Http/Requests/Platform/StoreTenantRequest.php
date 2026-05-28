<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('platform.manageTenants') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9\-]+$/', Rule::unique('tenants', 'subdomain')],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'status' => ['required', Rule::in(['active', 'trial', 'suspended'])],
            'admin_name' => ['nullable', 'string', 'max:255'],
            'admin_email' => ['nullable', 'email', 'max:255'],

            // School-setup template — required whenever the rich provisioning
            // path is taken (i.e. an admin email is supplied). May reference
            // either the numeric PK or the human-readable code (e.g.
            // GH_PRIMARY_JHS) for convenience.
            'school_template_id' => ['nullable', 'exists:school_templates,id'],
            'school_template_code' => ['nullable', 'string', 'exists:school_templates,code'],

            'academic_year_name' => ['nullable', 'string', 'max:32'],
            'include_kg' => ['nullable', 'boolean'],
            'create_default_fees' => ['nullable', 'boolean'],

            // Legacy options — kept so the older create form still works.
            'create_default_classes' => ['nullable', 'boolean'],
            'create_demo_data' => ['nullable', 'boolean'],
            'create_demo_users' => ['nullable', 'boolean'],
        ];
    }
}
