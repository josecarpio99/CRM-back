<?php

namespace App\Http\Requests\Deal;

use App\Enums\DealStatusEnum;
use App\Enums\DealTypeEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DealEstimatedCloseDateRangeEnum;

class UpdateDealRequest extends FormRequest
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
            'status' => [new Enum(DealStatusEnum::class)],
            'customer_id' => ['exists:customers,id'],
            'name' => ['required', 'max:255'],
            'value' => ['regex:/^\d+(\.\d{1,2})?$/'],
            'customer_responsiveness' => ['max:255'],
            'has_project_manager' => ['nullable', 'boolean'],
            // 'estimated_size' => ['numeric'],
            // 'win_probability' => ['numeric', 'min:0', 'max:100'],
            'owner_id' => ['exists:users,id'],
            'category_id' => ['exists:categories,id'],
            'source_id' => ['exists:sources,id'],
            'requirement' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'deal_pipeline_id' => ['exists:deal_pipelines,id'],
            'deal_pipeline_stage_id' => ['exists:deal_pipeline_stages,id'],
            'estimated_close_date_range' => [new Enum(DealEstimatedCloseDateRangeEnum::class)],
            'estimated_close_date' => ['date'],
            'added_at' => ['date'],
            'stage_moved_at' => ['date'],
            'discount' => ['numeric']
        ];
    }
}
