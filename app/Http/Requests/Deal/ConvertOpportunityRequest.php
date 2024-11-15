<?php

namespace App\Http\Requests\Deal;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DealEstimatedCloseDateRangeEnum;

class ConvertOpportunityRequest extends FormRequest
{
    /**
     * Determine if the customer is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'value' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'estimated_close_date_range' => [
                'required',
                new Enum(DealEstimatedCloseDateRangeEnum::class)
            ]
        ];
    }
}
