<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'SuperAdmin';
    case Proprietor = 'Proprietor';
    case Admin = 'Admin';
    case Accountant = 'Accountant';
    case Teacher = 'Teacher';
    case Parent = 'Parent';
    case Student = 'Student';

    // Phase 5 extension roles. Existing role:Foo middleware groups still work;
    // these are *additional* roles that the permission layer maps to a smaller
    // surface area than full Admin/Proprietor.
    case Headteacher = 'Headteacher';
    case ExamOfficer = 'ExamOfficer';
    case AttendanceOfficer = 'AttendanceOfficer';
    case Librarian = 'Librarian';
    case InventoryOfficer = 'InventoryOfficer';
    case DisciplineOfficer = 'DisciplineOfficer';
}
