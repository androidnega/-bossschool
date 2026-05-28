<?php

namespace App\Http\Requests\Platform;

use App\Enums\UserRole;
use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantUserRequest extends FormRequest
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
        $tid = (int) $tenant->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where(fn ($q) => $q->where('tenant_id', $tid)),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                'string',
                Rule::in([
                    UserRole::Proprietor->value,
                    UserRole::Admin->value,
                    UserRole::Accountant->value,
                    UserRole::Teacher->value,
                    UserRole::Parent->value,
                    UserRole::Student->value,
                ]),
            ],
            'student_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn () => $this->input('role') === UserRole::Student->value),
                Rule::exists('students', 'id')->where(fn ($q) => $q->where('tenant_id', $tid)),
            ],
        ];
    }
}
