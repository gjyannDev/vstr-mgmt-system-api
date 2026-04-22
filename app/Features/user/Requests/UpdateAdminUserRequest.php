<?php

namespace App\Features\user\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminUserRequest extends FormRequest
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
        $admin = $this->route('admin');
        $adminId = is_object($admin) ? $admin->id : $admin;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($adminId),
            ],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'location_id' => ['sometimes', 'nullable', 'uuid', 'exists:locations,id'],
            'location_ids' => ['sometimes', 'array', 'min:1'],
            'location_ids.*' => ['required', 'uuid', 'distinct', 'exists:locations,id'],
        ];
    }
}
