<?php

namespace App\Rules;

use App\Support\GhanaPhone as GhanaPhoneSupport;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that a phone number is a plausible Ghanaian mobile or
 * landline number. Delegates parsing to {@see GhanaPhoneSupport::parse()}
 * so the rule and the mutators always agree on what "valid" means.
 */
class GhanaPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return; // Use 'required' separately if needed.
        }

        if (! GhanaPhoneSupport::isValid((string) $value)) {
            $fail(__('The :attribute must be a valid Ghana phone number (e.g. 0241234567 or +233241234567).', ['attribute' => $attribute]));
        }
    }
}
