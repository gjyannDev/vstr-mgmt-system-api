<?php

namespace App\Features\user\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignAdminLocationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $locationId = $this->input('location_id');
        $locationIds = $this->input('location_ids');

        if (is_string($locationId) && (! is_array($locationIds) || $locationIds === [])) {
            $this->merge([
                'location_ids' => [$locationId],
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'location_id' => ['sometimes', 'nullable', 'uuid', 'exists:locations,id'],
            'location_ids' => ['required', 'array', 'min:1'],
            'location_ids.*' => ['required', 'uuid', 'distinct', 'exists:locations,id'],
        ];
    }
}
