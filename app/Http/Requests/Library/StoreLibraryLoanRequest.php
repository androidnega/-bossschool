<?php

namespace App\Http\Requests\Library;

use App\Models\LibraryLoan;
use Illuminate\Foundation\Http\FormRequest;

class StoreLibraryLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LibraryLoan::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'library_book_id' => ['required', 'integer', 'exists:library_books,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'staff_id' => ['nullable', 'integer', 'exists:staff,id'],
            'borrowed_at' => ['required', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:borrowed_at'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $studentId = $this->input('student_id');
            $staffId = $this->input('staff_id');
            if (! $studentId && ! $staffId) {
                $v->errors()->add('student_id', __('Choose a student or a staff borrower.'));
            }
            if ($studentId && $staffId) {
                $v->errors()->add('staff_id', __('Pick either student OR staff — not both.'));
            }
        });
    }
}
