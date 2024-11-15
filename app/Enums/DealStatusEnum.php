<?php

namespace App\Enums;

enum DealStatusEnum : string
{
    case New = 'nuevo';
    case Won = 'ganado';
    case Lost = 'perdido';
    case InProgress = 'en proceso';
}
