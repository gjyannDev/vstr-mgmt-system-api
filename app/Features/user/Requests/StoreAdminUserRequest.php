<?php

namespace App\Features\user\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'location_id' => ['sometimes', 'nullable', 'uuid', 'exists:locations,id'],
            'location_ids' => ['required', 'array', 'min:1'],
            'location_ids.*' => ['required', 'uuid', 'distinct', 'exists:locations,id'],
        ];
    }
}
