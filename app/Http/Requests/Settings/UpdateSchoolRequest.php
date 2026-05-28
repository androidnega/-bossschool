<?php

namespace App\Http\Requests\Settings;

use App\Rules\GhanaPhone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Restrict to safe raster image types only. SVG is intentionally
            // excluded because SVG files can carry inline <script> payloads.
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:64', new GhanaPhone],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:32', 'regex:/^\d{4}\/\d{4}$/'],
            'ges_region' => ['nullable', 'string', 'max:64'],
            'ges_district' => ['nullable', 'string', 'max:64'],
            'ges_circuit' => ['nullable', 'string', 'max:64'],
            'school_code' => ['nullable', 'string', 'max:32'],
            'head_teacher_name' => ['nullable', 'string', 'max:128'],
            'motto' => ['nullable', 'string', 'max:191'],
        ];
    }
}
