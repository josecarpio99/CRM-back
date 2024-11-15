<?php

namespace App\Http\Requests\User;

use App\Enums\RoleEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user->id)
            ],
            'name' => ['required', 'max:255'],
            'branch' => ['required', 'max:255'],
            'password' => ['nullable', 'min:8', 'max:255'],
            'role' => ['required', new Enum(RoleEnum::class)]
        ];
    }
}
