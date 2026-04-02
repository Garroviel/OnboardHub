<?php

namespace App\Enum;

enum OnboardingStatus: string
{
    case ACTIVE = 'ACTIVE';
    case COMPLETED = 'COMPLETED';
    case ARCHIVED = 'ARCHIVED';
}