<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class StorePlatformNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sendPlatformNotice') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
        ];
    }
}
