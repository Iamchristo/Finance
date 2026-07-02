<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Vendor = 'vendor';
    case SupportAgent = 'support_agent';
    case Moderator = 'moderator';
    case Administrator = 'administrator';
    case SuperAdministrator = 'super_administrator';
}
