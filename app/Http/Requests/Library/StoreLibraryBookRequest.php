<?php

namespace App\Http\Requests\Library;

use App\Models\LibraryBook;
use Illuminate\Foundation\Http\FormRequest;

class StoreLibraryBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LibraryBook::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:191'],
            'isbn' => ['nullable', 'string', 'max:32'],
            'category' => ['nullable', 'string', 'max:64'],
            'copies_total' => ['required', 'integer', 'min:1', 'max:10000'],
            'shelf_location' => ['nullable', 'string', 'max:64'],
        ];
    }
}
