<?php

namespace App\Enum;

enum UserRole: string
{
    case COLLABORATOR = 'COLLABORATOR';
    case MANAGER = 'MANAGER';
    case HR = 'HR';
    case ADMIN = 'ADMIN';
}