<?php

namespace App\Http\Requests\EndOfTerm;

use Illuminate\Foundation\Http\FormRequest;

class ReopenEndOfTermRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reopen', $this->route('end_of_term_run')) ?? false;
    }

    public function rules(): array
    {
        return [
            'reopen_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
