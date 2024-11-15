<?php

namespace App\Http\Requests\Lead;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteLeadRequest extends FormRequest
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
            'leads' => ['required', 'array'],
            'leads.*.id' => ['required', 'exists:leads,id']
        ];
    }
}
