<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'Admin';
    case Proprietor = 'Proprietor';
    case Accountant = 'Accountant';
    case Teacher = 'Teacher';
}
