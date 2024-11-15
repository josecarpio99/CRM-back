<?php

namespace App\Http\Requests\Deal;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AttachDetachDealContactRequest extends FormRequest
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
            'type' => ['required', 'in:attach,detach'],
            'contact_id' => [
                'required',
                'exists:contacts,id',
                Rule::when(
                    $this->type == 'attach',
                    Rule::unique('contact_deal', 'contact_id')->where('deal_id', $this->deal->id)
                )
            ],
        ];
    }

    public function messages()
    {
        return [
            'contact_id.unique' => 'El contacto ya ha sido asociado'
        ];
    }
}
