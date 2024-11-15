<?php

namespace App\Http\Requests\Lead;

use App\Enums\LeadStatusEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'company_name' => ['max:255'],
            'razon_social' => ['max:255'],
            'city' => ['max:255'],
            // 'name' => ['max:255'],
            // 'mobile' => ['max:20'],
            // 'email' => ['email', 'max:255'],
            'phone' => ['nullable', 'max:20'],
            'owner_id' => ['exists:users,id'],
            'requirement' => ['nullable'],
            'category_id' => ['exists:categories,id'],
            'source_id' => ['exists:sources,id']
        ];
    }
}
