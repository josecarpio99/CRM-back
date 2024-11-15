<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
            'company_name' => ['max:255'],
            'razon_social' => ['max:255'],
            // 'name' => ['max:255'],
            // 'mobile' => ['max:20'],
            // 'email' => ['email', 'max:255'],
            'city' => ['max:255'],
            'source_id' => ['exists:sources,id'],
            'owner_id' => ['exists:users,id'],
            'category_id' => ['exists:categories,id'],
            'logo' => ['nullable', 'image']
        ];
    }

    public function attributes(): array
    {
        return [
            'parent_id' => 'compañia matriz',
        ];
    }
}
