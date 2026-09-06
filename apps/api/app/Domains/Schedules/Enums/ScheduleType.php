<?php

namespace App\Domains\Schedules\Enums;

enum ScheduleType: string
{
    case Shooting = 'SHOOTING';
    case Meeting = 'MEETING';
    case InternalReview = 'INTERNAL_REVIEW';
    case ClientReview = 'CLIENT_REVIEW';
    case Deadline = 'DEADLINE';
}
