<?php

namespace App\Features\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKioskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location_id' => ['required', 'uuid', 'exists:locations,id'],
            'visit_type_id' => ['nullable', 'uuid', 'exists:visit_types,id'],
            'visit_type_ids' => ['nullable', 'array'],
            'visit_type_ids.*' => ['uuid', 'exists:visit_types,id'],
            'status' => ['nullable', 'string', 'in:active,disabled'],
        ];
    }
}
