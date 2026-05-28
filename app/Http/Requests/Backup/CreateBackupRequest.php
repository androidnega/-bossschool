<?php

namespace App\Http\Requests\Backup;

use App\Models\TenantBackup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TenantBackup::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'backup_type' => ['required', 'string', Rule::in(TenantBackup::TYPES)],
        ];
    }
}
