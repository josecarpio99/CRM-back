<?php

namespace App\Enums;

enum DealEstimatedCloseDateRangeEnum : string
{
    case fromZeroToThreeMonths = 'de 0 a 3 meses';
    case fromThreeToSixMonths = 'de 3 a 6 meses';
    case sixMonthsOrMore = '6 meses o más';
}
