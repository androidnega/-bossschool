<?php

namespace App\Enums;

enum MessageRecipientType: string
{
    case AllParents = 'all_parents';
    case ClassParents = 'class_parents';
    case SelectedParent = 'selected_parent';
    case AllStudents = 'all_students';
    case ClassStudents = 'class_students';
    case Teachers = 'teachers';
    case AllUsers = 'all_users';
    case PlatformTenants = 'platform_tenants';

    public function label(): string
    {
        return match ($this) {
            self::AllParents => __('All parents'),
            self::ClassParents => __('Class parents'),
            self::SelectedParent => __('Selected parent'),
            self::AllStudents => __('All students'),
            self::ClassStudents => __('Class students'),
            self::Teachers => __('Teachers'),
            self::AllUsers => __('All school users'),
            self::PlatformTenants => __('All schools (platform)'),
        };
    }

    public static function tryFromString(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value);
    }

    /** @return list<string> */
    public static function schoolAudienceValues(): array
    {
        return [
            self::AllParents->value,
            self::ClassParents->value,
            self::SelectedParent->value,
            self::AllStudents->value,
            self::ClassStudents->value,
            self::Teachers->value,
            self::AllUsers->value,
        ];
    }
}
