<?php

namespace App\Http\Requests\Contact;

use App\Enums\ContactRelationEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
            'id' => ['integer', 'required'],
            'contact_type' => ['required', new Enum(ContactRelationEnum::class)],
            'name' => ['required', 'max:255'],
            'phone' => ['max:20'],
            'email' => ['email', 'max:255'],
            'email2' => ['email', 'max:255'],
            'phone2' => ['max:20']
        ];
    }

    public function getModel(): Model
    {
        $model = ContactRelationEnum::getInstance($this->contact_type);

        return $model->findOrFail($this->id);
    }
}
