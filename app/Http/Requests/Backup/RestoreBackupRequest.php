<?php

namespace App\Http\Requests\Backup;

use Illuminate\Foundation\Http\FormRequest;

class RestoreBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'target_tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'confirm' => ['required', 'string', 'in:RESTORE'],
            'password' => ['required', 'string'],
        ];
    }
}
