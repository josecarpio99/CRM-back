<?php

namespace App\Http\Requests\SmartList;

use Illuminate\Foundation\Http\FormRequest;

class StoreSmartListRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'resource_type' => ['required', 'max:255'],
            'name' => ['required', 'max:255'],
            'definition' => ['required']
        ];
    }
}
