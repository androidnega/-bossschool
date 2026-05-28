<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validates the one-shot SuperAdmin bootstrap form. Anyone can submit it,
 * but the controller refuses to act once a SuperAdmin already exists, so
 * this request only fires during initial setup.
 */
class SuperAdminSetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        // No auth check here — this form runs BEFORE any user exists. The
        // controller enforces "no existing SuperAdmin" gating.
        return ! User::query()
            ->withoutGlobalScopes()
            ->whereNull('tenant_id')
            ->where('role', UserRole::SuperAdmin->value)
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->numbers(),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
        ];
    }
}
