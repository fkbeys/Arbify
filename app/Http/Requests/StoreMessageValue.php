<?php

namespace App\Http\Requests;

use App\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageValue extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'required',
            'form' => [
                'sometimes',
                Rule::in(array_keys(Language::PLURAL_FORMS) + Language::GENDER_FORMS),
            ]
        ];
    }
}
