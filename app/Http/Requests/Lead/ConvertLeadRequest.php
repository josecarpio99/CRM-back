<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertLeadRequest extends FormRequest
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
            'star' => ['boolean'],
            'company_name' => ['required', 'max:255'],
            'razon_social' => ['max:255'],
            // 'name' => ['required', 'max:255'],
            // 'mobile' => ['max:20'],
            // 'email' => ['email', 'max:255'],
            'requirement' => ['string'],
            'city' => ['max:255'],
            'source_id' => ['exists:sources,id'],
            'owner_id' => ['required', 'exists:users,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'create_opportunity' => ['boolean']
        ];
    }

    public function attributes(): array
    {
        return [
            'parent_id' => 'compañia matriz',
        ];
    }
}
