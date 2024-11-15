<?php

namespace App\Http\Requests\Document;

use App\Enums\DocumentModelEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDocumentRequest extends FormRequest
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
            'model_id' => ['integer', 'required'],
            'model_type' => ['required', new Enum(DocumentModelEnum::class)],
            'collection' => ['required', 'in:files,profitability,client'],
            'file' => [
                'required',
                'file',
                'max:40000'
            ]
        ];
    }
}
