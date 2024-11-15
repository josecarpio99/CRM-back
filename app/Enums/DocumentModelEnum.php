<?php

namespace App\Enums;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Model;

enum DocumentModelEnum : string
{
    case Customer = 'customer';
    case Lead = 'lead';
    case Deal  = 'deal';

    public static function getInstance(string $type) : Model
    {
        return match($type)
        {
            DocumentModelEnum::Customer->value => new Customer(),
            DocumentModelEnum::Lead->value => new Lead(),
            DocumentModelEnum::Deal->value => new Deal()
        };
    }
}
