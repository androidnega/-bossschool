<?php

namespace App\Http\Requests\Message;

use App\Enums\MessageRecipientType;
use App\Enums\UserRole;
use App\Models\Message;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->can('create', Message::class)
            || $user->can('sendFeeReminder', Message::class)
            || $user->can('sendClassNotice', Message::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $audiences = MessageRecipientType::schoolAudienceValues();

        return [
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
            'recipient_type' => ['required', 'string', Rule::in($audiences)],
            'school_class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'recipient_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();
            if ($user === null) {
                return;
            }

            $type = (string) $this->input('recipient_type');
            $classId = $this->filled('school_class_id') ? (int) $this->input('school_class_id') : null;
            $recipientUserId = $this->filled('recipient_user_id') ? (int) $this->input('recipient_user_id') : null;

            if (in_array($type, [
                MessageRecipientType::ClassParents->value,
                MessageRecipientType::ClassStudents->value,
            ], true) && $classId === null) {
                $validator->errors()->add('school_class_id', __('A class is required for this audience.'));
            }

            if ($type === MessageRecipientType::SelectedParent->value && $recipientUserId === null) {
                $validator->errors()->add('recipient_user_id', __('Select a parent user.'));
            }

            if ($user->role === UserRole::Accountant->value && ! in_array($type, [
                MessageRecipientType::AllParents->value,
                MessageRecipientType::ClassParents->value,
            ], true)) {
                $validator->errors()->add('recipient_type', __('Accountants may only send fee reminders to parents.'));
            }

            if ($user->role === UserRole::Teacher->value) {
                if (! $user->can('sendClassNotice', Message::class)) {
                    $validator->errors()->add('recipient_type', __('Not allowed.'));
                }
                if (! in_array($type, [
                    MessageRecipientType::ClassParents->value,
                    MessageRecipientType::ClassStudents->value,
                ], true)) {
                    $validator->errors()->add('recipient_type', __('Teachers may only send class notices.'));
                }
                if ($classId === null || ! $user->assignedClasses()->where('classes.id', $classId)->exists()) {
                    $validator->errors()->add('school_class_id', __('Pick one of your assigned classes.'));
                }
            }

            if ($classId !== null) {
                $class = SchoolClass::query()->whereKey($classId)->first();
                if ($class && (int) $class->tenant_id !== (int) $user->tenant_id) {
                    $validator->errors()->add('school_class_id', __('Invalid class.'));
                }
            }

            if ($recipientUserId !== null) {
                $target = User::query()->whereKey($recipientUserId)->first();
                if ($target && (int) $target->tenant_id !== (int) $user->tenant_id) {
                    $validator->errors()->add('recipient_user_id', __('Invalid user.'));
                }
                if ($type === MessageRecipientType::SelectedParent->value && $target && $target->role !== UserRole::Parent->value) {
                    $validator->errors()->add('recipient_user_id', __('Selected user must be a parent.'));
                }
            }
        });
    }
}
