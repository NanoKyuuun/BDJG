<?php

namespace App\Domains\Users\Enums;

enum UserStatus: string
{
    case Invited = 'INVITED';
    case Active = 'ACTIVE';
    case Suspended = 'SUSPENDED';
    case Disabled = 'DISABLED';
}
