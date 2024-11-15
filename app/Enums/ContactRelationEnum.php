<?php

namespace App\Enums;

use App\Models\Lead;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

enum ContactRelationEnum : string
{
    case Customer = 'customer';
    case Lead = 'lead';

    public static function getInstance(string $type) : Model
    {
        return match($type)
        {
            ContactRelationEnum::Customer->value => new Customer(),
            ContactRelationEnum::Lead->value => new Lead()
        };
    }

    public static function getMorphClass(string $type) : string
    {
        return match($type)
        {
            ContactRelationEnum::Customer->value => Customer::class,
            ContactRelationEnum::Lead->value => Lead::class
        };
    }

}
