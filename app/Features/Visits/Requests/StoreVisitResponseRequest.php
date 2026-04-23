<?php

namespace App\Features\Visits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visit_type_id' => ['required', 'uuid', 'exists:visit_types,id'],
            'session_key' => ['nullable', 'uuid'],
            'is_final' => ['boolean'],
            'visitor' => ['nullable', 'array'],
            'visitor.full_name' => ['required_with:visitor', 'string', 'max:255'],
            'visitor.email' => ['nullable', 'email', 'max:255'],
            'visitor.phone' => ['nullable', 'string', 'max:50'],
            'visitor.company' => ['nullable', 'string', 'max:255'],
            'visitor.id' => ['nullable', 'uuid', 'exists:visitors,id'],
            'form_data' => ['nullable', 'array'],
            'form_data.*' => ['nullable'],
            'image_url' => ['nullable', 'url'],
            'image_base64' => ['nullable', 'string'],
        ];
    }
}
