<?php

namespace App\Features\Visits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'requires_approval' => ['boolean'],
            'active' => ['boolean'],
            'form_fields' => ['nullable', 'array'],
            'form_fields.*.id' => ['sometimes', 'exists:form_fields,id'],
            'form_fields.*.label' => ['required', 'string', 'max:255'],
            'form_fields.*.name' => ['required', 'string', 'max:255'],
            'form_fields.*.type' => ['required', 'string', 'max:100'],
            'form_fields.*.required' => ['boolean'],
            'form_fields.*.options' => ['nullable', 'array'],
            'form_fields.*.validation_rules' => ['nullable', 'array'],
            'form_fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'form_fields.*.is_system' => ['boolean'],
            'form_fields.*.sort_order' => ['nullable', 'integer'],
        ];
    }
}
