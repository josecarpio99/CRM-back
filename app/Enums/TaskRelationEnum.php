<?php

namespace App\Enums;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Model;

enum TaskRelationEnum : string
{
    case Customer = 'customer';
    case Lead = 'lead';
    case Deal  = 'deal';

    public static function getInstance(string $type) : Model
    {
        return match($type)
        {
            NoteRelationEnum::Customer->value => new Customer(),
            NoteRelationEnum::Lead->value => new Lead(),
            NoteRelationEnum::Deal->value => new Deal()
        };
    }
}
