<?php

namespace App\Http\Requests\Deal;

use App\Enums\RoleEnum;
use App\Enums\DealTypeEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DealEstimatedCloseDateRangeEnum;

class StoreDealRequest extends FormRequest
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
            'type' => [new Enum(DealTypeEnum::class)],
            'customer_id' => [
                Rule::requiredIf(isset($this->customer_id)),
                'exists:customers,id'
            ],
            'contact_id' => [
                'nullable',
                'exists:contacts,id'
            ],
            'customer' => [
                'array'
            ],
            'customer.name' => [
                Rule::requiredIf(! isset($this->customer_id)),
                'string',
                'max:255'
            ],
            'customer.company_name' => [
                Rule::requiredIf(! isset($this->customer_id)),
                'max:255'
            ],
            'customer.category_id' => [
                Rule::requiredIf(! isset($this->customer_id)),
                'exists:categories,id'
            ],
            'customer.email' => ['nullable', 'email','max:255'],
            'customer.mobile' => ['nullable', 'string','max:255'],
            'name' => ['required', 'max:255'],
            'requirement' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'value' => [
                Rule::requiredIf($this->user()->role != RoleEnum::LeadQualifier->value),
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            // 'win_probability' => ['required','numeric', 'min:0', 'max:100'],
            'owner_id' => ['required', 'exists:users,id'],
            // 'category_id' => ['required', 'exists:categories,id'],
            'source_id' => ['required', 'exists:sources,id'],
            'deal_pipeline_id' => ['exists:deal_pipelines,id'],
            'deal_pipeline_stage_id' => ['exists:deal_pipeline_stages,id'],
            'estimated_close_date_range' => ['required', new Enum(DealEstimatedCloseDateRangeEnum::class)],
            'estimated_close_date' => ['date'],
            'added_at' => ['date'],
            'stage_moved_at' => ['date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer' => 'contacto',
            'customer.name' => 'nombre del contacto',
            'customer.company_name' => 'nombre de la empresa',
            'customer.category_id' => 'clasificación de la cuenta',
        ];
    }
}
