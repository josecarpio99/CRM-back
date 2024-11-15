<?php

namespace App\Http\Requests\Deal;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AttachDetachContactRequest extends FormRequest
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
            'customer_id' => [
                'required',
                'exists:customers,id',
                Rule::when(
                    $this->type == 'attach',
                    Rule::unique('customer_deal', 'customer_id')->where('deal_id', $this->deal->id)
                )
            ],
        ];
    }

    public function messages()
    {
        return [
            'customer_id.unique' => 'El contacto ya ha sido asociado'
        ];
    }
}
