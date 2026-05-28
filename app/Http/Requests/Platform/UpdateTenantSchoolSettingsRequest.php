<?php

namespace App\Http\Requests\Platform;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantSchoolSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Tenant $tenant */
        $tenant = $this->route('tenant');

        return [
            'tenant_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('tenants', 'subdomain')->ignore($tenant->id),
            ],
            'tenant_status' => ['required', 'string', Rule::in([
                Tenant::STATUS_ACTIVE,
                Tenant::STATUS_SUSPENDED,
                Tenant::STATUS_TRIAL,
            ])],
            'trial_end' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => $this->input('tenant_status') === Tenant::STATUS_TRIAL),
            ],
            'school_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:64'],
        ];
    }
}
