<?php

namespace App\Enums;

enum LeadStatusEnum : string
{
    case New = 'nuevo';
    case Assigned = 'asignado';
    case Unqualified  = 'no calificado';
}
