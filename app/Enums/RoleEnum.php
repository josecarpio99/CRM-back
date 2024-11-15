<?php

namespace App\Enums;

enum RoleEnum : string
{
    case Superadmin = 'superadmin';
    case Director = 'director';
    case Admin = 'admin';
    case LeadQualifier = 'lead_qualifier';
    case TeamLeader = 'team_leader';
    case Advisor  = 'advisor';
}
