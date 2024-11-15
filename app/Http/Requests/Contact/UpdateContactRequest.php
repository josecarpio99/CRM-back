<?php

namespace App\Http\Requests\Contact;

use App\Enums\NoteRelationEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
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
            'name' => ['required', 'max:255'],
            'phone' => ['max:20'],
            'email' => ['email', 'max:255'],
        ];
    }
}
