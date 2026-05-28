<?php

namespace App\Http\Requests\EndOfTerm;

use App\Models\EndOfTermRun;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEndOfTermRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('end_of_term_run')) ?? false;
    }

    public function rules(): array
    {
        $checklistKeys = EndOfTermRun::DEFAULT_CHECKLIST;

        return [
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'action' => ['nullable', 'string', 'in:save,close'],
        ];
    }
}
