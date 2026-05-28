<?php

namespace App\Http\Requests\Permissions;

use Illuminate\Foundation\Http\FormRequest;

class SyncUserPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'role' => ['nullable', 'string', 'max:32'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'max:64'],
        ];
    }
}
