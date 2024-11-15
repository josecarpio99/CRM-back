<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskRelationEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
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
            'owner_id' => ['required', 'exists:users,id'],
            'id' => ['integer', 'required'],
            'task_type' => ['required', new Enum(TaskRelationEnum::class)],
            'content' => ['required', 'max:500'],
            'due_at' => ['required', 'date']
        ];
    }
}
