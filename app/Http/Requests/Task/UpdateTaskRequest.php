<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskRelationEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTaskRequest extends FormRequest
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
            'user_id' => ['exists:users,id'],
            'owner_id' => ['exists:users,id'],
            'content' => ['max:500'],
            'done' => ['boolean'],
            'done' => ['boolean'],
            'done_by' => ['nullable', 'exists:users,id'],
            'due_at' => ['date'],
            'due_date' => ['date'],
        ];
    }
}
