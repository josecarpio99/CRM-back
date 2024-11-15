<?php

namespace App\Http\Requests\Note;

use App\Enums\NoteRelationEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreNoteRequest extends FormRequest
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
            'id' => ['integer', 'required'],
            'note_type' => ['required', new Enum(NoteRelationEnum::class)],
            'content' => ['required']
        ];
    }
}
