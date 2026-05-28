<?php

namespace App\Http\Requests\EndOfTerm;

use App\Models\EndOfTermRun;
use Illuminate\Foundation\Http\FormRequest;

class StoreEndOfTermRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EndOfTermRun::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'term_id' => ['required', 'integer', 'exists:terms,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
